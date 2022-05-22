<?php

require_once __DIR__.'/../vendor/autoload.php';

use Skeletal\Support\Log;
use Skeletal\Support\Response;

$app = Skeletal::getInstance();

$app->handleRequest(function (Response $response) {

    $collection = collect();

    debug('----------');

    $collection->set('address.street_address', '123 Manchester Road');
    $collection->set('address.city', 'Manchester');
    $collection->set('address.postcode.a', 'a');

    $collection->set('address.postcode.b', 'b');

    $collection->set('address.postcode.a', 'c');

    $collection->set('test', 5);
    $collection->unset('test');

    $collection->merge(['test 1'], ['test 2'], ['test 3'])->values();

    return Response::json($collection);
});
