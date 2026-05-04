<?php
require 'vendor/autoload.php';
use Illuminate\Support\Facades\Http;
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$url = "https://wagateway.surakana.my.id";
$deviceId = config('services.whatsapp_gateway.device_id');
$response = Http::withBasicAuth("admin", "admin")
    ->withHeaders(['X-Device-Id' => $deviceId])
    ->attach('image', 'dummy image content', 'test.jpg')
    ->post($url . '/send/image', [
        'phone' => '6281234567890',
        'caption' => 'Test image attaching',
    ]);
echo "Multipart Image Status: " . $response->status() . "\n";
echo "Multipart Image Body: " . $response->body() . "\n";
