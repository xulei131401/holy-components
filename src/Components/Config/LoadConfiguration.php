<?php
/**
 * 加载目录下所有的.php配置文件
 */
namespace Holy\Components\Config;

use SplFileInfo;
use Symfony\Component\Finder\Finder;

class LoadConfiguration
{
    private static $config;
    private static $basePath;

    public static function register($basePath = null)
    {
        if ($basePath) {
            self::$basePath = $basePath;
            self::$config = new Repository([]);
            self::loadConfigurationFiles(self::$config);
            date_default_timezone_set(self::$config->get('app.timezone', 'PRC'));
            mb_internal_encoding('UTF-8');
        }
    }

    /**
     * @return mixed
     */
    public static function getConfigBasePath()
    {
        return self::$basePath;
    }

    /**
     * @return mixed
     */
    public static function getInstance()
    {
        return self::$config;
    }

    /**
     * 加载全部的配置文件
     * @param Repository $repository
     */
    public static function loadConfigurationFiles(Repository $repository)
    {
        foreach (self::getAllConfigurationFiles() as $key => $path) {
            $repository->set($key, require $path);
        }
    }

    /**
     * 解析全部的配置文件
     * @return array
     */
    public static function getAllConfigurationFiles()
    {
        $files = [];
        $configPath = realpath(self::getConfigBasePath());
        $finder = Finder::create()->files()->name('*.php')->in($configPath);
        foreach ($finder as $file) {
            $directory = self::getNestedDirectory($file, $configPath);
            $files[$directory.basename($file->getRealPath(), '.php')] = $file->getRealPath();
        }
        return $files;
    }

    /**
     * 获取嵌套的配置文件路径(如果是嵌套目录则返回目 录名+ . )
     * @param SplFileInfo $file
     * @param $configPath
     * @return string
     */
    public static function getNestedDirectory(SplFileInfo $file, $configPath)
    {
        $directory = $file->getPath();
        $nested = trim(str_replace($configPath, '', $directory), DIRECTORY_SEPARATOR);
        if ($nested) {
            $nested = str_replace(DIRECTORY_SEPARATOR, '.', $nested).'.';
        }
        return $nested;
    }
}