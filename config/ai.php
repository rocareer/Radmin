<?php
return [
    // redis 配置
    'redis'   => [
        'host'           => '',
        'port'           => 6379,
        'password'       => '',
        'select'         => false,
        'timeout'        => 10,
        'expire'         => 0,
        'persistent'     => false,
        'data_prefix'    => 'ai_content:',
        'index_name'     => 'ai_index:content_vss',
        // 操作失败直接抛出异常，不抛出则需要手动 ->getLastError()
        'fail_exception' => true,
    ],
    'debug'   => true,
    'log_dir' => base_path() . 'runtime' . DIRECTORY_SEPARATOR . 'ai' . DIRECTORY_SEPARATOR,
];