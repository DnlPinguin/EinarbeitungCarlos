<?php

namespace OCA\Doorman\AppInfo;

use OCA\Doorman\Dashboard\SimpleWidget;
use OCA\Doorman\Dashboard\StopWatch;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;

class Application extends App implements IBootstrap {
    public const APP_ID = 'doorman';
    public const INTEGRATION_USER_AGENT = 'Nextcloud Doorman Integration';
    public const DOORMAN_API_URL = 'https://umtsdevel.lucksmith.dev/';
    public const DOORMAN_OAUTH_ACCESS_URL = 'https://umtsdevel.lucksmith.dev/auth/login';

    public function __construct(array $urlParams = []) {
        parent::__construct(self::APP_ID, $urlParams);
    }

    public function register(IRegistrationContext $context): void {
        $context->registerDashboardWidget(SimpleWidget::class);
        $context->registerDashboardWidget(StopWatch::class);
    }

    public function boot(IBootContext $context): void {
    }
}