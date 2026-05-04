<?php
require 'vendor/autoload.php';
use Illuminate\Support\Facades\Http;
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$url = "https://wagateway.surakana.my.id";
$deviceId = config('services.whatsapp_gateway.device_id');
// try uploading a dummy txt file to /api/send/document (or /send/document)
$response = Http::withBasicAuth("admin", "admin")
    ->withHeaders(['X-Device-Id' => $deviceId])
    ->attach('file', 'dummy content', 'test.txt')
    ->post($url . '/send/document', [
        'phone' => '081234567890',
        'caption' => 'Test document attaching',
    ]);
echo "Multipart Doc Status: " . $response->status() . "\n";
echo "Multipart Doc Body: " . $response->body() . "\n";

$response2 = Http::withBasicAuth("admin", "admin")
    ->withHeaders(['X-Device-Id' => $deviceId])
    ->post($url . '/send/document', [
        'phone' => '081234567890',
        'document_url' => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf',
        'caption' => 'Test document attaching',
    ]);
echo "JSON Doc Status: " . $response2->status() . "\n";
echo "JSON Doc Body: " . $response2->body() . "\n";
