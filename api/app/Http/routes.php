<?php

$app->group([
    'prefix' => '/d',
    'middleware' => ['AccessControlAllowOrigin', 'UserAuth'],
    'namespace' => 'App\Http\Controllers'], function () use ($app) {

    $app->get('devices', 'DeviceController@devices');

    $app->get('device/{deviceId}/data', 'DataController@query');

    $app->post('send/{deviceId}', 'DataController@send');

    $app->post('group', 'GroupController@create');

    $app->get('device/types', 'DeviceTypeController@deviceTypes');

    $app->post('device/type', 'DeviceTypeController@create');

    $app->post('device/type/{typeId}', 'DeviceTypeController@modify');

    $app->post('device/type/{typeId}/fields', 'DeviceTypeController@modifyFields');

    $app->post('device', 'DeviceController@create');

});

$app->group([
    'prefix' => '/u',
    'middleware' => ['AccessControlAllowOrigin'],
    'namespace' => 'App\Http\Controllers'], function() use ($app) {

    // 注册
    $app->post('/register', 'UserController@register');

    // 登录
    $app->post('login', 'UserController@login');
});

$app->group([
    'prefix' => '/p',
    // 'middleware' => ['AccessControlAllowOrigin'],
    'namespace' => 'App\Http\Controllers'], function () use ($app) {

    $app->get('doc/list', 'DocController@pages');

    $app->get('doc/{docName}', 'DocController@render');

});