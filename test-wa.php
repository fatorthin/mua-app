<?php
$response = file_get_contents("https://wagateway.surakana.my.id/docs/swagger.json", false, stream_context_create([
    "http" => ["header" => "Authorization: Basic " . base64_encode("admin:admin")]
]));
echo strip_tags($response);
