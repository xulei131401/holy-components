<?php
/**
 * 加载环境变量配置
 */
namespace Holy\Components\Config;

use Dotenv\Dotenv;

class LoadEnvironmentVariables
{
    protected static $environmentFile = '.env';
    public static function register($basePath = null)
    {
        if ($basePath) {
            if (file_exists($basePath .DIRECTORY_SEPARATOR . self::$environmentFile)) {
                with(new Dotenv($basePath, self::$environmentFile))->load();
            }
        }
    }

    /**
     * 获取env文件名
     * @return string
     */
    public static function getEnvironmentFile()
    {
        return self::$environmentFile;
    }
}