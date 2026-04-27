<?php

// Test the API endpoint directly
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Create a test request
$user = App\Models\User::first();
auth()->login($user);

// Simulate the exact request the bell component makes
$request = new Illuminate\Http\Request();
$request->setMethod('GET');
$request->setUserResolver(fn() => auth()->user());
$request->headers->set('Accept', 'application/json');
$request->headers->set('X-Requested-With', 'XMLHttpRequest');

// Call the controller directly
$controller = new App\Http\Controllers\Api\NotificationController();

try {
    $response = $controller->index($request);
    echo "✅ Controller returned successfully\n";
    echo "Status: " . $response->getStatusCode() . "\n";
    echo "Headers:\n";
    foreach ($response->headers->all() as $key => $values) {
        echo "  $key: " . implode(', ', $values) . "\n";
    }
    echo "\nBody:\n";
    echo $response->getContent() . "\n";
} catch (\Exception $e) {
    echo "❌ Controller error:\n";
    echo "  Message: " . $e->getMessage() . "\n";
    echo "  File: " . $e->getFile() . "\n";
    echo "  Line: " . $e->getLine() . "\n";
}
