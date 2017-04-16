<?php

return [

    /*
      |--------------------------------------------------------------------------
      | PDO Fetch Style
      |--------------------------------------------------------------------------
      |
      | By default, database results will be returned as instances of the PHP
      | stdClass object; however, you may desire to retrieve records in an
      | array format for simplicity. Here you can tweak the fetch style.
      |
     */

    'fetch' => PDO::FETCH_CLASS,
    /*
      |--------------------------------------------------------------------------
      | Default Database Connection Name
      |--------------------------------------------------------------------------
      |
      | Here you may specify which of the database connections below you wish
      | to use as your default connection for all database work. Of course
      | you may use many connections at once using the Database library.
      |
     */
    'default' => env('DB_CONNECTION', 'mysql'),
    /*
      |--------------------------------------------------------------------------
      | Database Connections
      |--------------------------------------------------------------------------
      |
      | Here are each of the database connections setup for your application.
      | Of course, examples of configuring each database platform that is
      | supported by Laravel is shown below to make development simple.
      |
      |
      | All database work in Laravel is done through the PHP PDO facilities
      | so make sure you have the driver for your particular database of
      | choice installed on your machine before you begin development.
      |
     */
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'read' => [
                'host' => env('DB_READ_HOST') ? env('DB_READ_HOST') : env('DB_HOST'),
                'port' => env('DB_READ_PORT') ? env('DB_READ_PORT') : env('DB_PORT'),
                'database' => env('DB_READ_DATABASE') ? env('DB_READ_DATABASE') : env('DB_DATABASE'),
                'username' => env('DB_READ_USERNAME') ? env('DB_READ_USERNAME') : env('DB_USERNAME'),
                'password' => env('DB_READ_PASSWORD') ? env('DB_READ_PASSWORD') : env('DB_PASSWORD'),
                'prefix' => env('DB_READ_PREFIX') ? env('DB_READ_PREFIX') : env('DB_PREFIX'),
            ],
            'write' => [
                'host' => env('DB_HOST', 'localhost'),
                'port' => env('DB_PORT', '3306'),
                'database' => env('DB_DATABASE', 'test'),
                'username' => env('DB_USERNAME', 'test'),
                'password' => env('DB_PASSWORD', 'test'),
                'prefix' => env('DB_PREFIX', ''),
            ],


            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'timezone' => env('DB_TIMEZONE', '+08:00'),
            'strict' => false,
            'options' => array(
                PDO::ATTR_TIMEOUT => 1,
                PDO::ATTR_PERSISTENT => 0,
            ),
        ],

        'data_track_point' => [
            'driver' => 'mysql',
            'read' => [
                'host' => env('DB_READ_HOST_TRACK_POINT') ? env('DB_READ_HOST_TRACK_POINT') : env('DB_HOST_TRACK_POINT'),
                'port' => env('DB_READ_PORT_TRACK_POINT') ? env('DB_READ_PORT_TRACK_POINT') : env('DB_PORT_TRACK_POINT'),
                'database' => env('DB_READ_DATABASE_TRACK_POINT') ? env('DB_READ_DATABASE_TRACK_POINT') : env('DB_DATABASE_TRACK_POINT'),
                'username' => env('DB_READ_USERNAME_TRACK_POINT') ? env('DB_READ_USERNAME_TRACK_POINT') : env('DB_USERNAME_TRACK_POINT'),
                'password' => env('DB_READ_PASSWORD_TRACK_POINT') ? env('DB_READ_PASSWORD_TRACK_POINT') : env('DB_PASSWORD_TRACK_POINT'),
                'prefix' => env('DB_READ_PREFIX_TRACK_POINT') ? env('DB_READ_PREFIX_TRACK_POINT') : env('DB_PREFIX_TRACK_POINT'),
            ],
            'write' => [
                'host' => env('DB_HOST_TRACK_POINT', 'localhost'),
                'port' => env('DB_PORT_TRACK_POINT', '3306'),
                'database' => env('DB_DATABASE_TRACK_POINT', 'data_track_point'),
                'username' => env('DB_USERNAME_TRACK_POINT', 'dt_point'),
                'password' => env('DB_PASSWORD_TRACK_POINT', 'dt_point'),
                'prefix' => env('DB_PREFIX_TRACK_POINT', ''),
            ],

            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'timezone' => env('DB_TIMEZONE', '+08:00'),
            'strict' => false,
            'options' => array(
                PDO::ATTR_TIMEOUT => 1,
                PDO::ATTR_PERSISTENT => 0,
            ),
        ],
    ],
    /*
      |--------------------------------------------------------------------------
      | Migration Repository Table
      |--------------------------------------------------------------------------
      |
      | This table keeps track of all the migrations that have already run for
      | your application. Using this information, we can determine which of
      | the migrations on disk haven't actually been run in the database.
      |
     */
    'migrations' => 'migrations',
    /*
      |--------------------------------------------------------------------------
      | Redis Databases
      |--------------------------------------------------------------------------
      |
      | Redis is an open source, fast, and advanced key-value store that also
      | provides a richer set of commands than a typical key-value systems
      | such as APC or Memcached. Laravel makes it easy to dig right in.
      |
     */
    'redis' => [

        'cluster' => env('REDIS_CLUSTER', false),
        'default' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_DATABASE', 0),
            //'password' => env('REDIS_PASSWORD', null),
            'timeout' => 1,
            //'persistent' => 1,
            //'read_write_timeout' => 1
        ],
        'redis_session' => [
            'host' => env('SESSION_REDIS_HOST', '127.0.0.1'),
            'database' => env('SESSION_REDIS_DATABASE', 0),
            'port' => env('SESSION_REDIS_PORT', 6379),
            'password' => env('SESSION_REDIS_PASSWORD', null),
            'timeout' => 0.2,
            'persistent' => 1,
            'read_write_timeout' => 0.2
        ],
        'ftz_redis' => [
            'host' => env('FTZ_REDIS_MASTER_HOST', '127.0.0.1'),
            'port' => env('FTZ_REDIS_PORT', 6379),
            'database' => env('FTZ_REDIS_DATABASE', 0),
            //'password' => env('REDIS_PASSWORD', null),
            'timeout' => 0.2,
            'persistent' => 1,
            'read_write_timeout' => 0.2
        ],

    ],
    //图片相关
    'api' => [
        /*
         * 普通接口超时秒数设置
         */
        'timeout' => 10,
        'connect_timeout' => 10,

    ],
    'upload' => [
        /*
         * 上传文件接口超时秒数设置
         */
        'timeout' => 15,
        'connect_timeout' => 5,
    ],
    'download' => [
        /*
         * 下载文件接口超时秒数设置
         */
        'timeout' => 30,
        'connect_timeout' => 5,
    ],
    /**
     * 商户登录接口
     * 
     */
    'login' => [
        'merchant'=> trim(env('XAPI_URL'),'/').'/mapp/v1/'
    ]
];

