<?php
require 'vendor/autoload.php';
use Illuminate\Support\Facades\Http;
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$url = "https://wagateway.surakana.my.id";
$deviceId = config('services.whatsapp_gateway.device_id');
$response = Http::withBasicAuth("admin", "admin")
    ->withHeaders(['X-Device-Id' => $deviceId])
    ->attach('file', 'document pdf test', 'test.pdf')
    ->post($url . '/send/file', [
        'phone' => '6281234567890',
        'caption' => 'Test file attaching',
    ]);
echo "Multipart File Status: " . $response->status() . "\n";
echo "Multipart File Body: " . $response->body() . "\n";
