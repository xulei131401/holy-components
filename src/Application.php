<?php
namespace Holy;

class Application
{
    const VERSION = '1.0.0';
    protected $basePath;
    protected $databasePath;
    protected $environmentPath;
    protected $monologConfigurator;
    protected $environmentFile = '.env';

    public function __construct($basePath = null)
    {
        if ($basePath) {
            $this->setBasePath($basePath);
            $this->registerEnvironment();
        }
    }
    protected function registerEnvironment()
    {
        with(new Environment($this->basePath))->overload();
    }

    public function version()
    {
        return static::VERSION;
    }

    public function setBasePath($basePath)
    {
        $this->basePath = rtrim($basePath, '\/');
        return $this;
    }

    public function configPath()
    {
        return $this->basePath.DIRECTORY_SEPARATOR.'config';
    }

    public function databasePath()
    {
        return $this->databasePath ?: $this->basePath.DIRECTORY_SEPARATOR.'database';
    }

    public function environmentPath()
    {
        return $this->environmentPath ?: $this->basePath;
    }

    public function useEnvironmentPath($path)
    {
        $this->environmentPath = $path;

        return $this;
    }
    public function loadEnvironmentFrom($file)
    {
        $this->environmentFile = $file;

        return $this;
    }

    public function environmentFile()
    {
        return $this->environmentFile ?: '.env';
    }

    public function environmentFilePath()
    {
        return $this->environmentPath().'/'.$this->environmentFile();
    }

    public function isLocal()
    {
        return $this['env'] == 'local';
    }

    public function configureMonologUsing(callable $callback)
    {
        $this->monologConfigurator = $callback;

        return $this;
    }

    public function hasMonologConfigurator()
    {
        return ! is_null($this->monologConfigurator);
    }

    public function getMonologConfigurator()
    {
        return $this->monologConfigurator;
    }
}