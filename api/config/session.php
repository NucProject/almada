<?php

return [
    'driver' => 'file',

    'lottery' => [0, 999],

    'files' => '/tmp/php_session',

    'lifetime' => 7200,

    'expire_on_close' => false,

    'path' => '/',

    'domain' => null,

    'cookie' => 'session',
];