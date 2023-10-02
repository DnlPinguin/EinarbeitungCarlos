<?php

namespace OCA\Doorman\Controller;

use OC\User\NoUserException;
use OCA\Doorman\Service\GifService;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataDownloadResponse;
use OCP\AppFramework\Services\IInitialState;
use OCP\Files\InvalidPathException;
use OCP\Files\NotFoundException;
use OCP\Files\NotPermittedException;
use OCP\Lock\LockedException;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class GifController extends Controller {
	/**
	 * @var string|null
	 */
	private $userId;
	/**
	 * @var GifService
	 */
	private $gifService;

	public function __construct(string        $appName,
								IRequest      $request,
								IInitialState $initialStateService,
								GifService    $gifService,
								?string       $userId)
	{
		parent::__construct($appName, $request);
		$this->initialStateService = $initialStateService;
		$this->userId = $userId;
		$this->gifService = $gifService;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @param int $fileId
	 * @return DataDownloadResponse|DataResponse
	 * @throws InvalidPathException
	 * @throws NoUserException
	 * @throws NotFoundException
	 * @throws NotPermittedException
	 * @throws LockedException
	 */
	public function getGifFile(int $fileId) {
		$file = $this->gifService->getGifFile($this->userId, $fileId);
		if ($file !== null) {
			$response = new DataDownloadResponse(
				$file->getContent(),
				'',
				$file->getMimeType()
			);
			$response->cacheFor(60 * 60);
			return $response;
		}

		return new DataResponse('', Http::STATUS_NOT_FOUND);
	}
}