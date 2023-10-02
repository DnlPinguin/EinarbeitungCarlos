<?php

namespace OCA\Doorman\Dashboard;

use OCA\Doorman\Service\GifService;
use OCP\AppFramework\Services\IInitialState;
use OCP\Dashboard\IAPIWidget;
use OCP\IL10N;

use OCA\Doorman\AppInfo\Application;
use OCP\Util;

class SimpleWidget implements IAPIWidget {


    private $l10n;
    private $gifService;
    private $initialStateService;
    private $userId;

    public function __construct(IL10N $l10n,
                                GifService $gifService,
                                IInitialState $initialStateService,
                                ?string $userId) {
        $this->l10n = $l10n;
        $this->gifService = $gifService;
        $this->initialStateService = $initialStateService;
        $this->userId = $userId;
    }

    public function getId(): string {
        return 'doorman-simple-widget';
    }

    public function getTitle(): string {
        return $this->l10n->t('Simple widget');
    }

    public function getOrder(): int {
        return 10;
    }

    public function getIconClass(): string {
        return 'icon-doorman';
    }

    public function getUrl(): ?string {
        return null;
    }

    public function load(): void {
        if ($this->userId !== null) {
            $items = $this->getItems($this->userId);
            $this->initialStateService->provideInitialState('dashboard-widget-items', $items);
        }

        Util::addScript(Application::APP_ID, Application::APP_ID . '-dashboardSimple');
        Util::addStyle(Application::APP_ID, 'dashboard');
    }

    public function getItems(string $userId, ?string $since = null, int $limit = 7): array {
        return $this->gifService->getWidgetItems($userId);
    }
}