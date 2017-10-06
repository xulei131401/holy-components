<?php
namespace Holy\Components\Other\OSS;

use OSS\OssClient;

class XLOssClient
{
    protected static $_instance = null;
    private  function __construct(){}
    private function __clone() {}
    public static function getOssClient()
    {
        if (is_null ( self::$_instance ) || isset ( self::$_instance )) {
            self::$_instance = new OssClient(env('OSS_ACCESS_ID'), env('OSS_ACCESS_KEY'), env('OSS_ENDPOINT'), false, env('OSS_SECURITY_TOKEN'));
        }
        return self::$_instance;
    }
}