<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\\Contracts\\Http\\Kernel');
$requestClass = 'Illuminate\\Http\\Request';
$request = $requestClass::create('/', 'GET');
$response = $kernel->handle($request);
echo $response->getStatusCode();
$kernel->terminate($request, $response);
