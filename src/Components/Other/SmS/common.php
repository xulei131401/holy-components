<?php

return [
    //上传文件目录
    'UPLOAD_DIR' => '/resource' . DS . 'uploads' . DS,
    //话费多充值相关配置
    'HUAFEIDUO' => [
        'API_KEY'    => 'QPwDm2XPx3oBRBK0zba1guJbMWZvPHUvmfZz8LduDrVkTvjhwj9DDGFfrtB82krd',
        'SECRET_KEY' => 'qgCmRFGkXquGLDN9NLBfg8lypMugXMjcSLozgUK5a5zMxcK0NaeU8qhm3CsOeCQX',
        'NOTIFY_URL' => '',//'http://baiyin789.top/putao/phonebill/notify'
        'GATE_WAY'   => 'http://api.huafeiduo.com/gateway.cgi'
    ],
    //短信配置
    'SMS' => [
        'AGENT' => 'TECENT',
        'TECENT' => [
            'APP_ID'  => '1400116879',
            'APP_KEY' => '2911038f85faa9361441532ae0e91630'
        ]
    ],
    //自己定义的LOG配置，与TP框架无关
    'LOG' => [
        'default' => [
            'file'      => 'run.log',                 		// 日志文件名
            'appender'  => 'day',						    // 文件增长方式：null-单个文件追加；'day|month|year'-按天|月|年增长
            'pattern'   => '[%d %t][%p][%s][%i]%m%n',		// 自定义状态,支持特殊字符替换，
            'level'     => 5,
            'save_path' => LOG_PATH . '_zlog' . DS,
        ]
    ]
];