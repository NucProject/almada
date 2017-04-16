<?php

$app->group([
    'prefix' => '/d',
    'middleware' => ['AccessControlAllowOrigin'],
    'namespace' => 'App\Http\Controllers'], function () use ($app) {

    $app->get('test', function() {

    });

});