<?php
/**
 * 加载环境变量配置
 */
namespace Holy\Components\Config;

use Dotenv\Dotenv;

class LoadEnvironmentVariables
{
    protected $environmentFile = '.env';
    public function __construct($basePath = null)
    {
        if ($basePath) {
            if (file_exists($basePath .DIRECTORY_SEPARATOR . $this->environmentFile)) {
                with(new Dotenv($basePath, $this->environmentFile))->load();
            }
        }
    }

    /**
     * 获取env文件名
     * @return string
     */
    public function getEnvironmentFile()
    {
        return $this->environmentFile;
    }
}