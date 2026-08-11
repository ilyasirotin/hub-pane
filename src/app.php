<?php

declare(strict_types=1);

// APP

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/router.php';

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$module = HubPane\resolveModule($requestUri);

if ($module === null) {
    http_response_code(404);
    $pageTitle = 'Not Found';
    $moduleContent = '<h1>404 — Page Not Found</h1>';
} else {
    $pageTitle = $module->getTitle();
    $moduleContent = $module->render();
}

// UI

require __DIR__ . '/../templates/index.phtml';
