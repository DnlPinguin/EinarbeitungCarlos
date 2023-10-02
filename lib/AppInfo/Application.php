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