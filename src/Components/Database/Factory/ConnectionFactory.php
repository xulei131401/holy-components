<?php

namespace Holy\Components\Database\Factory;

use Holy\Components\Database\Connectors\MySqlConnector;
use Holy\Components\Primary\Arr;
use InvalidArgumentException;

class ConnectionFactory
{
    /**
     * 利用工厂进行创建
     * @param array $config
     * @param null $name
     * @return MySqlConnection
     */
    public function make(array $config, $name = null)
    {
        return $this->createSingleConnection($this->parseConfig($config, $name));
    }

    /**
     * 添加表前缀
     * @param array $config
     * @param $name
     * @return mixed
     */
    protected function parseConfig(array $config, $name)
    {
        return Arr::add(Arr::add($config, 'prefix', ''), 'name', $name);
    }

    /**
     * 创建普通单连接
     * @param array $config
     * @return MySqlConnection
     */
    protected function createSingleConnection(array $config)
    {
        $pdo = $this->createPdoResolverWithHost($config);
        return $this->createConnection(
            $config['driver'], $pdo, $config['database'], $config['prefix'], $config
        );
    }

    /**
     * 创建PDO连接
     * @param array $config
     * @return \Closure
     */
    protected function createPdoResolverWithHost(array $config)
    {
        return function () use ($config) {
            return $this->createConnector($config)->connect($config);
        };
    }

    /**
     * 依据配置创建PDO连接
     * @param array $config
     * @return MySqlConnector
     */
    public function createConnector(array $config)
    {
        if (! isset($config['driver'])) {
            throw new InvalidArgumentException('A driver must be specified.');
        }

        switch ($config['driver']) {
            case 'mysql':
                return new MySqlConnector;
        }

        throw new InvalidArgumentException("Unsupported driver [{$config['driver']}]");
    }

    /**
     * 创建一个应用数据库连接
     * @param $driver
     * @param $connection
     * @param $database
     * @param string $prefix
     * @param array $config
     * @return MySqlConnection
     */
    protected function createConnection($driver, $connection, $database, $prefix = '', array $config = [])
    {
        switch ($driver) {
            case 'mysql':
                return new MySqlConnection($connection, $database, $prefix, $config);
        }

        throw new InvalidArgumentException("Unsupported driver [$driver]");
    }
}
