<?php

require_once __DIR__.'/../vendor/autoload.php';

use Skeletal\Support\Log;
use Skeletal\Support\Response;
use Skeletal\Support\Collection;

$app = Skeletal::getInstance();

$app->handleRequest(function (Response $response) {

    $collection = collect([
        'address' => [
            'street_address' => '123 Manchester Road',
            'city' => 'Manchester',
            'postcode' => 'M1 1FT'
        ]
    ]);

    debug('----------');

    $address = $collection->get('address')->sortKeys();
    return Response::json($address->toArray());
});
