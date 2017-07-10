<?php

$app->group([
    'prefix' => '/d',
    'middleware' => ['AccessControlAllowOrigin', 'UserAuth'],
    'namespace' => 'App\Http\Controllers'], function () use ($app) {

    $app->get('devices', 'DeviceController@devices');

    $app->get('device/{deviceId}/data', 'DataController@query');

    $app->get('device/{deviceId}/latest', 'DataController@latest');

    $app->get('device/hpge/{deviceId}/data', 'HpgeController@query');

    $app->get('device/hpge/{deviceId}/nuclide', 'HpgeController@nuclide');

    $app->get('device/hpge/{deviceId}/download/{dataId}', 'HpgeController@download');

    $app->get('device/cinderella/{deviceId}/data', 'CinderellaController@query');

    $app->get('device/weather/{deviceId}/data', 'WeatherController@query');

    // 发送数据(Form)
    $app->post('send/{deviceId}', 'DataController@send');

    // 发送文件
    $app->post('file/{deviceId}', 'DataController@file');

    // 发送文件
    $app->get('download/file', 'DataController@download');

    $app->post('group', 'GroupController@create');

    $app->get('device/types', 'DeviceTypeController@deviceTypes');

    $app->post('device/type', 'DeviceTypeController@create');

    $app->post('device/type/{typeId}', 'DeviceTypeController@modify');

    $app->post('device/type/{typeId}/fields', 'DeviceTypeController@modifyFields');

    $app->post('device', 'DeviceController@create');

    //命令相关
    $app->get('cmd/send/history/{deviceId}', 'CommandController@sendHistoryCommand');

    $app->get('cmd/fetch/history/{deviceId}', 'CommandController@fetchHistoryCommand');
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