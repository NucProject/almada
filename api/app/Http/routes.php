<?php

$app->group([
    'prefix' => '/d',
    'middleware' => ['AccessControlAllowOrigin', 'UserAuth'],
    'namespace' => 'App\Http\Controllers'], function () use ($app) {

    // 设备相关
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

    $app->post('upload/{deviceId}', 'DataController@upload');

    // 发送文件
    $app->get('download/file', 'DataController@download');

    // 用户组相关
    $app->post('group', 'GroupController@create');

    $app->get('device/types', 'DeviceTypeController@deviceTypes');

    $app->post('device/type', 'DeviceTypeController@create');

    $app->post('device/type/{typeId}', 'DeviceTypeController@modify');

    $app->post('device/type/{typeId}/fields', 'DeviceTypeController@modifyFields');

    $app->post('device', 'DeviceController@create');

    //命令相关
    $app->get('cmd/send/history/{deviceId}', 'CommandController@sendHistoryCommand');

    $app->get('cmd/fetch/history/{deviceId}', 'CommandController@fetchHistoryCommand');

    // 自动站相关
    $app->get('stations', 'StationController@stations');

    $app->post('station', 'StationController@create');
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