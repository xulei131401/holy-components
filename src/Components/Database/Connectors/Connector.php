<?php

namespace Holy\Components\Database\Connectors;

use Holy\Components\Primary\Arr;
use Holy\Database\Traits\LostConnectionTraits;
use PDO;
use Exception;

class Connector
{
    use LostConnectionTraits;
    /**
     * 设置PDO操作属性，可参考文档设置更多
     * @var array
     */
    protected $options = [
        PDO::ATTR_CASE => PDO::CASE_NATURAL,    //保留数据库驱动返回的列名
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,    //如果发生错误，则抛出一个 PDOException异常
        PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,    //不转换 NULL 和空字符串
        PDO::ATTR_STRINGIFY_FETCHES => false,           //提取的时候将数值转换为字符串
        PDO::ATTR_EMULATE_PREPARES => false,            //禁用强制PDO总是模拟预处理语句
    ];

    /**
     * 创建一个数据库连接
     * @param $dsn
     * @param array $config
     * @param array $options
     * @return PDO
     */
    public function createConnection($dsn, array $config, array $options)
    {
        list($username, $password) = [Arr::get($config, 'username'), Arr::get($config, 'password'),];
        try {
            return $this->createPdoConnection($dsn, $username, $password, $options);
        } catch (Exception $e) {
            // 若连接失败则进尝试重新连接
            return $this->tryAgainConnection($e, $dsn, $username, $password, $options);
        }
    }

    /**
     * 创建PDO连接
     * @param $dsn
     * @param $username
     * @param $password
     * @param $options
     * @return PDO
     */
    protected function createPdoConnection($dsn, $username, $password, $options)
    {
        if (! $this->isPersistentConnection($options)) {

        }
        return new PDO($dsn, $username, $password, $options);
    }

    /**
     * 判断连接是不是持久连接.
     *
     * @param  array  $options
     * @return bool
     */
    protected function isPersistentConnection($options)
    {
        // 如果想使用持久连接，必须在传递给 PDO 构造函数的驱动选项数组中设置 PDO::ATTR_PERSISTENT
        return isset($options[PDO::ATTR_PERSISTENT]) && $options[PDO::ATTR_PERSISTENT];
    }

    /**
     * 重新建立数据库连接
     * @param Exception $e
     * @param $dsn
     * @param $username
     * @param $password
     * @param $options
     * @return PDO
     * @throws Exception
     */
    protected function tryAgainConnection(Exception $e, $dsn, $username, $password, $options)
    {
        if ($this->reasonByLostConnection($e)) {
            return $this->createPdoConnection($dsn, $username, $password, $options);
        }
        throw $e;
    }

    /**
     * 获取不同于默认的PDO配置项
     *
     * @param  array  $config
     * @return array
     */
    public function getOptions(array $config)
    {
        $options = Arr::get($config, 'options', []);
        return array_diff_key($this->options, $options) + $options;
    }

    /**
     * 获取默认的PDO配置项
     *
     * @return array
     */
    public function getDefaultOptions()
    {
        return $this->options;
    }

    /**
     * 设置PDO默认配置项
     *
     * @param  array  $options
     * @return void
     */
    public function setDefaultOptions(array $options)
    {
        $this->options = $options;
    }

}
