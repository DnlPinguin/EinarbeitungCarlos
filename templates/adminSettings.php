<?php

use OCA\Doorman\AppInfo\Application;

$appId = OCA\Doorman\AppInfo\Application::APP_ID;
// \OCP\Util::addScript($appId, $appId . '-adminSettings');
\OCP\Util::addScript(Application::APP_ID, Application::APP_ID . '-adminSettingsVue');
\OCP\Util::addStyle(Application::APP_ID, 'adminSettings');
?>


