<?php
require 'vendor/autoload.php';
use Illuminate\Support\Facades\Http;
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$url = "https://wagateway.surakana.my.id";
$auth = "admin:admin";
// try uploading a dummy txt file to /send/document
$response = Http::withBasicAuth("admin", "admin")
    ->attach('file', 'dummy content', 'test.txt')
    ->post($url . '/send/document', [
        'phone' => '6281234567890',
        'caption' => 'Test document attaching',
    ]);
echo "Doc Status: " . $response->status() . "\n";
echo "Doc Body: " . $response->body() . "\n";
