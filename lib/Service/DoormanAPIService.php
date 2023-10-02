<?php
/**
 * Nextcloud - Doorman
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Julien Veyssier <julien-nc@posteo.net>
 * @author Anupam Kumar <kyteinsky@gmail.com>
 * @copyright Julien Veyssier 2022
 * @copyright Anupam Kumar 2023
 */

namespace OCA\Doorman\Service;

use Datetime;
use Exception;

use OC\Files\Node\File;
use OC\Files\Node\Folder;
use OC\User\NoUserException;
use OCP\Constants;
use OCP\Files\IRootFolder;
use OCP\Files\NotPermittedException;
use OCP\Http\Client\IClient;
use OCP\Http\Client\IClientService;
use OCP\IConfig;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Lock\LockedException;
use OCP\PreConditionNotMetException;
use OCP\Security\ICrypto;
use OCP\Share\IManager as ShareManager;
use OCP\Share\IShare;
use Psr\Log\LoggerInterface;

use OCA\Doorman\AppInfo\Application;
use OCA\Doorman\Service\NetworkService;

/**
 * Service to make requests to Doorman API
 */
class DoormanAPIService {

    private IClient $client;
    private LoggerInterface $logger;
    private IL10N $l10n;
    private IConfig $config;
    private IRootFolder $root;
    private ShareManager $shareManager;
    private IURLGenerator $urlGenerator;
    private ICrypto $crypto;
    private \OCA\Doorman\Service\NetworkService $networkService;

    public function __construct(
        LoggerInterface $logger,
        IL10N $l10n,
        IConfig $config,
        IRootFolder $root,
        ShareManager $shareManager,
        IURLGenerator $urlGenerator,
        ICrypto $crypto,
        NetworkService $networkService,
        IClientService $clientService
    ) {
        $this->networkService = $networkService;
        $this->crypto = $crypto;
        $this->urlGenerator = $urlGenerator;
        $this->shareManager = $shareManager;
        $this->root = $root;
        $this->config = $config;
        $this->l10n = $l10n;
        $this->logger = $logger;
        $this->client = $clientService->newClient();
    }


    /**
     * @param string $userId
     * @param string $doormanUserId
     * @return string|null
     */
    private function getUserRealName(string $userId, string $doormanUserId): string|null {
        $userInfo = $this->request($userId, 'users.info', ['user' => $doormanUserId]);
        if (isset($userInfo['error'])) {
            return null;
        }
        if (!isset($userInfo['user'], $userInfo['user']['real_name'])) {
            return null;
        }
        return $userInfo['user']['real_name'];
    }

    private function startTimeTracking(): void {
        $this->logger->info('Starting time tracking', ['app' => Application::APP_ID]);
    }

    private function stopTimeTracking(): void {
        $this->logger->info('Stopping time tracking', ['app' => Application::APP_ID]);
    }

    private function startPause(): void {
        $this->logger->info('Starting pause', ['app' => Application::APP_ID]);
    }

    private function stopPause(): void {
        $this->logger->info('Stopping pause', ['app' => Application::APP_ID]);
    }

    /**
     * @param string $userId
     * @param string $endPoint
     * @param array $params
     * @param string $method
     * @param bool $jsonResponse
     * @param bool $doormanApiRequest
     * @return array|mixed|resource|string|string[]
     * @throws PreConditionNotMetException
     */
    public function request(string $userId, string $endPoint, array $params = [], string $method = 'GET',
                            bool $jsonResponse = true, bool $doormanApiRequest = true) {
        $this->checkTokenExpiration($userId);
        return $this->networkService->request($userId, $endPoint, $params, $method, $jsonResponse, $doormanApiRequest);
    }

    /**
     * @param string $userId
     * @return void
     * @throws \OCP\PreConditionNotMetException
     */
    private function checkTokenExpiration(string $userId): void {
        $refreshToken = $this->config->getUserValue($userId, Application::APP_ID, 'refresh_token');
        $expireAt = $this->config->getUserValue($userId, Application::APP_ID, 'token_expires_at');
        if ($refreshToken !== '' && $expireAt !== '') {
            $nowTs = (new Datetime())->getTimestamp();
            $expireAt = (int) $expireAt;
            // if token expires in less than a minute or is already expired
            if ($nowTs > $expireAt - 60) {
                $this->refreshToken($userId);
            }
        }
    }

    /**
     * @param string $userId
     * @return bool
     * @throws \OCP\PreConditionNotMetException
     */
    private function refreshToken(string $userId): bool {
        $clientID = $this->config->getAppValue(Application::APP_ID, 'client_id');
        $clientSecret = $this->config->getAppValue(Application::APP_ID, 'client_secret');
        $refreshToken = $this->config->getUserValue($userId, Application::APP_ID, 'refresh_token');

        if (!$refreshToken) {
            $this->logger->error('No Doorman refresh token found', ['app' => Application::APP_ID]);
            return false;
        }

        try {
            $clientSecret = $this->crypto->decrypt($clientSecret);
        } catch (Exception $e) {
            $this->logger->error('Unable to decrypt Doorman secrets', ['app' => Application::APP_ID]);
            return false;
        }

        $result = $this->requestOAuthAccessToken(Application::DOORMAN_OAUTH_ACCESS_URL, [
            'client_id' => $clientID,
            'client_secret' => $clientSecret,
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
        ], 'POST');

        if (isset($result['access_token'])) {
            $this->logger->info('Doorman access token successfully refreshed', ['app' => Application::APP_ID]);

            $accessToken = $result['access_token'];
            $refreshToken = $result['refresh_token'];
            $this->config->setUserValue($userId, Application::APP_ID, 'token', $accessToken);
            $this->config->setUserValue($userId, Application::APP_ID, 'refresh_token', $refreshToken);

            if (isset($result['expires_in'])) {
                $nowTs = (new Datetime())->getTimestamp();
                $expiresAt = $nowTs + (int) $result['expires_in'];
                $this->config->setUserValue($userId, Application::APP_ID, 'token_expires_at', $expiresAt);
            }

            return true;
        } else {
            // impossible to refresh the token
            $this->logger->error(
                'Token is not valid anymore. Impossible to refresh it: '
                . $result['error'] ?? '' . ' '
            . $result['error_description'] ?? '[no error description]',
                ['app' => Application::APP_ID]
            );

            return false;
        }
    }

    /**
     * @param string $url
     * @param array $params
     * @param string $method
     * @return array
     */
    public function requestOAuthAccessToken(string $url, array $params = [], string $method = 'GET'): array {
        try {
            $options = [
                'headers' => [
                    'User-Agent'  => Application::INTEGRATION_USER_AGENT,
                ]
            ];

            if (count($params) > 0) {
                if ($method === 'GET') {
                    $paramsContent = http_build_query($params);
                    $url .= '?' . $paramsContent;
                } else {
                    $options['body'] = $params;
                }
            }

            if ($method === 'GET') {
                $response = $this->client->get($url, $options);
            } else if ($method === 'POST') {
                $response = $this->client->post($url, $options);
            } else if ($method === 'PUT') {
                $response = $this->client->put($url, $options);
            } else if ($method === 'DELETE') {
                $response = $this->client->delete($url, $options);
            } else {
                return ['error' => $this->l10n->t('Bad HTTP method')];
            }
            $body = $response->getBody();
            $respCode = $response->getStatusCode();

            if ($respCode >= 400) {
                return ['error' => $this->l10n->t('OAuth access token refused')];
            } else {
                return json_decode($body, true);
            }
        } catch (Exception $e) {
            $this->logger->warning('Doorman OAuth error : '.$e->getMessage(), ['app' => Application::APP_ID]);
            return ['error' => $e->getMessage()];
        }
    }
}
