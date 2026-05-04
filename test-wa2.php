<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$url = url('/');
echo "Target: $url\n";
echo substr(file_get_contents($url), 0, 50);
