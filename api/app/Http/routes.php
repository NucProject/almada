<?php

$app->group([
    'prefix' => '/d',
    'middleware' => ['AccessControlAllowOrigin'],
    'namespace' => 'App\Http\Controllers'], function () use ($app) {

    // 设备相关
    $app->get('devices', 'DeviceController@devices');

    $app->get('allDevices', 'DeviceController@allDevices');

    $app->get('device/{deviceId}', 'DeviceController@deviceInfo');

    $app->get('device/{deviceId}/data', 'DataController@query');

    $app->get('device/{deviceId}/alerts', 'DataController@alerts');

    $app->get('device/{deviceId}/latestAlerts', 'AlertController@getLatestUnclearAlerts');

    $app->get('device/{deviceId}/latest', 'DataController@latest');

    $app->get('device/{deviceId}/ratio', 'DataController@ratio');

    $app->get('device/hpge/{deviceId}/data', 'HpgeController@query');

    $app->get('device/hpge/{deviceId}/nuclide', 'HpgeController@nuclide');

    $app->get('device/hpge/{deviceId}/download/{dataId}', 'HpgeController@download');

    $app->get('device/cinderella/{deviceId}/data', 'CinderellaController@query');

    $app->get('device/weather/{deviceId}/data', 'WeatherController@query');

    $app->get('device/weather/{deviceId}/wind', 'WeatherController@windInfo');

    $app->get('device/em/{deviceId}/e/{n}', 'EMDeviceController@eData');

    // 发送数据(Form)
    $app->post('send/{deviceId}', 'DataController@send');

    // 发送文件
    $app->post('file/{deviceId}', 'DataController@file');

    $app->post('upload/{deviceId}', 'DataController@upload');


    $app->get('alert/clear/device/{deviceId}', 'AlertController@clearAlertsByDeviceId');

    $app->get('alert/clear/id/{alertId}', 'AlertController@clearAlertsById');

    // 发送文件
    $app->get('download/file', 'DataController@download');


    $app->get('deviceTypes', 'DeviceTypeController@deviceTypes');

    $app->post('deviceType', 'DeviceTypeController@create');

    $app->post('deviceType/{typeId}', 'DeviceTypeController@modify');

    $app->post('deviceType/{typeId}/fields', 'DeviceTypeController@modifyFields');

    $app->get('deviceType/{typeId}/fields', 'DeviceTypeController@getFields');

    $app->post('device', 'DeviceController@create');

    //命令相关
    $app->get('cmd/send/history/{deviceId}', 'CommandController@sendHistoryCommand');

    $app->get('cmd/fetch/history/{deviceId}', 'CommandController@fetchHistoryCommand');



    $app->post('station', 'StationController@create');

    $app->post('station/{stationId}', 'StationController@modify');

    $app->post('station/{stationId}/remove', 'StationController@remove');

    // Alert
    $app->get('device/{deviceId}/alertConfigs', 'AlertController@alertConfigs');

    $app->post('device/{deviceId}/alertConfigs', 'AlertController@setAlertConfigs');

    $app->get('redis/setValue', 'RedisController@setValue');


    $app->get('offline/alerts', "AlertController@getOfflineAlerts");
});


$app->group([
    'prefix' => '/d',
    'middleware' => ['AccessControlAllowOrigin', 'Session', 'UserAuth'],
    'namespace' => 'App\Http\Controllers'], function () use ($app) {

    // 获取自动站, 并且聚合每个自动站下面的devices
    $app->get('stations', 'StationController@stations');

    $app->get('typedDevices', 'DeviceController@typedDevices');
});

// 需要鉴权的!
$app->group([
    'prefix' => '/u',
    'middleware' => ['AccessControlAllowOrigin', 'Session', 'UserAuth'],
    'namespace' => 'App\Http\Controllers'], function() use ($app) {

    // 注册
    $app->post('register', 'UserController@register');

    $app->get('invite', 'GroupController@inviteCode');

    // 用户组相关
    $app->post('group', 'GroupController@create');

    $app->get('users', 'GroupController@users');

    $app->get('info', 'UserController@info');
});

// 不需要鉴权的, 但是需要Session支持的
$app->group([
    'prefix' => '/u',
    'middleware' => ['AccessControlAllowOrigin', 'Session'],
    'namespace' => 'App\Http\Controllers'], function() use ($app) {

    // 注册
    $app->post('register', 'UserController@register');

    // 登录
    $app->post('login', 'UserController@login');
});



$app->group([
    'prefix' => '/p',
    'middleware' => ['AccessControlAllowOrigin'],
    'namespace' => 'App\Http\Controllers'], function () use ($app) {

    $app->get('now', 'DocController@now');

});


$app->group([
    'prefix' => '/p',
    'middleware' => [],
    'namespace' => 'App\Http\Controllers'], function () use ($app) {

    $app->get('doc/list', 'DocController@pages');

    $app->get('doc/{docName}', 'DocController@render');

});