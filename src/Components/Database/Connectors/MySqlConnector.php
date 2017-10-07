<?php

namespace Holy\Components\Database\Connectors;

use PDO;

class MySqlConnector extends Connector implements ConnectorInterface
{
    /**
     * 建立mysql连接
     * @param array $config
     * @return PDO
     */
    public function connect(array $config)
    {
        $dsn = $this->getDsn($config);
        $options = $this->getOptions($config);
        $connection = $this->createConnection($dsn, $config, $options);
        if (! empty($config['database'])) {
            $connection->exec("use `{$config['database']}`;");  // 默认使用配置数据库
        }
        $this->configureEncoding($connection, $config);
        $this->configureTimezone($connection, $config);
        $this->setModes($connection, $config);
        return $connection;
    }

    /**
     * 设置字符编码和校验集
     * @param $connection
     * @param array $config
     */
    protected function configureEncoding(PDO $connection, array $config)
    {
        if (isset($config['charset'])) {
            $connection->prepare("set names '{$config['charset']}'".$this->getCollation($config))->execute();
        }
    }

    /**
     * 获取校验集
     * @param array $config
     * @return string
     */
    protected function getCollation(array $config)
    {
        return ! is_null($config['collation']) ? " collate '{$config['collation']}'" : '';
    }

    /**
     * 设置时区
     * @param $connection
     * @param array $config
     */
    protected function configureTimezone(PDO $connection, array $config)
    {
        if (isset($config['timezone'])) {
            $connection->prepare('set time_zone="'.$config['timezone'].'"')->execute();
        }
    }

    /**
     * 优先获取socket方式的DSN配置
     * @param array $config
     * @return string
     */
    protected function getDsn(array $config)
    {
        return $this->hasSocket($config) ? $this->getSocketDsn($config) : $this->getHostDsn($config);
    }

    /**
     * 确定给定的配置数组是否具有socket
     * @param array $config
     * @return bool
     */
    protected function hasSocket(array $config)
    {
        return isset($config['unix_socket']) && ! empty($config['unix_socket']);
    }

    /**
     * 获取socket方式的DSN配置项
     * @param array $config
     * @return string
     */
    protected function getSocketDsn(array $config)
    {
        return "mysql:unix_socket={$config['unix_socket']};dbname={$config['database']}";
    }

    /**
     * 获取常规DSN配置项
     * @param array $config
     * @return string
     */
    protected function getHostDsn(array $config)
    {
        extract($config, EXTR_SKIP);    // EXTR_SKIP如果有冲突，不覆盖已有的变量
        return isset($port)
                    ? "mysql:host={$host};port={$port};dbname={$database}"
                    : "mysql:host={$host};dbname={$database}";
    }

    /**
     * 设置连接的sql_mode
     * @param PDO $connection
     * @param array $config
     */
    protected function setModes(PDO $connection, array $config)
    {
        if (isset($config['modes'])) {
            $this->setCustomModes($connection, $config);
        } elseif (isset($config['strict'])) {
            if ($config['strict']) {
                $connection->prepare($this->strictMode())->execute();
            } else {
                // 当创建表指定的engine不被支持时，直接报错
                $connection->prepare("set session sql_mode='NO_ENGINE_SUBSTITUTION'")->execute();
            }
        }
    }

    /**
     * 设置自定义sql_mode
     * @param PDO $connection
     * @param array $config
     */
    protected function setCustomModes(PDO $connection, array $config)
    {
        $modes = implode(',', $config['modes']);
        $connection->prepare("set session sql_mode='{$modes}'")->execute();
    }

    /**
     * 严格模式的sql_mode
     * @return string
     */
    protected function strictMode()
    {
        return "set session sql_mode='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'";
    }
}
