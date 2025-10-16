<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

try {
    $sessionService = $app->make('App\Services\SessionService');
    $session = $sessionService->createSession('127.0.0.1');
    echo "Session created successfully: " . $session->session_code . PHP_EOL;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
    echo "Trace: " . $e->getTraceAsString() . PHP_EOL;
}