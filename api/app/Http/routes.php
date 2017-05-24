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

    $app->post('group/', 'GroupController@create');

    $app->post('device/type', 'DeviceTypeController@create');

    $app->post('device/type/{$typeId}', 'DeviceTypeController@modifyFields');

});

$app->group([
    'prefix' => '/p',
    // 'middleware' => ['AccessControlAllowOrigin'],
    'namespace' => 'App\Http\Controllers'], function () use ($app) {

    $app->get('doc/list', 'DocController@pages');

    $app->get('doc/{docName}', 'DocController@render');

});