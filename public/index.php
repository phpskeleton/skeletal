<?php

require_once __DIR__.'/../vendor/autoload.php';

use Skeletal\Support\Log;
use Skeletal\Support\Response;

$app = Skeletal::getInstance();

$app->handleRequest(function (Response $response, Log $log) {
    return Response::json([
        'status' => 'success'
    ]);
});
