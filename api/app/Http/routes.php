<?php

$app->group([
    'prefix' => '/d',
    'middleware' => ['AccessControlAllowOrigin'],
    'namespace' => 'App\Http\Controllers'], function () use ($app) {

    $app->get('test', function() {
        echo "Hello lumen";
    });

    $app->get('devices', 'DeviceController@devices');

    $app->post('send/{deviceId}', 'DataController@send');

});