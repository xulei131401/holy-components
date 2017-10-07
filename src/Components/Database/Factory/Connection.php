<?php

namespace Holy\Components\Database\Factory;

use DateTimeInterface;
use Holy\Components\Primary\Arr;
use PDO;
use Closure;
use Exception;
use PDOStatement;
use LogicException;
use Holy\Components\Database\Traits\DetectsDeadlocks;
use Holy\Components\Database\Traits\DetectsLostConnections;
use Holy\Components\Database\Traits\ManagesTransactions;

class Connection implements ConnectionInterface
{
    use DetectsDeadlocks, DetectsLostConnections, ManagesTransactions;

    protected $pdo;
    protected $readPdo;
    protected $database;
    protected $tablePrefix = '';
    protected $config = [];
    protected $reconnector;
    protected $queryGrammar;
    protected $schemaGrammar;
    protected $postProcessor;
    protected $events;
    protected $fetchMode = PDO::FETCH_OBJ;

    protected $transactions = 0;
    protected $queryLog = [];
    protected $loggingQueries = false;
    protected $pretending = false;
    protected $doctrineConnection;
    protected static $resolvers = [];

    public function __construct($pdo, $database = '', $tablePrefix = '', array $config = [])
    {
        $this->pdo = $pdo;
        $this->database = $database;
        $this->tablePrefix = $tablePrefix;
        $this->config = $config;
    }


    /**
     * 原生select语句，返回第一条数据
     * @param $query
     * @param array $bindings
     * @param bool $useReadPdo
     * @return mixed
     */
    public function selectOne($query, $bindings = [], $useReadPdo = true)
    {
        return array_shift($this->select($query, $bindings, $useReadPdo));
    }

    /**
     * 直接运行原生select语句
     * @param $query
     * @param array $bindings
     * @param bool $useReadPdo
     * @return mixed
     */
    public function select($query, $bindings = [], $useReadPdo = true)
    {
        return $this->run($query, $bindings, function ($query, $bindings) use ($useReadPdo) {
            if ($this->pretending()) {
                return [];
            }
            $statement = $this->prepared($this->getPdoForSelect($useReadPdo)->prepare($query));
            $this->bindValues($statement, $this->prepareBindings($bindings));
            $statement->execute();
            return $statement->fetchAll();
        });
    }

    /**
     * 处理日期类的参数绑定
     * @param array $bindings
     * @return array
     */
    public function prepareBindings(array $bindings)
    {
        foreach ($bindings as $key => $value) {
            if ($value instanceof DateTimeInterface) {
                $bindings[$key] = $value->format('Y-m-d H:i:s');
            } elseif ($value === false) {
                $bindings[$key] = 0;
            }
        }
        return $bindings;
    }

    /**
     * 处理参数绑定
     * @param $statement
     * @param $bindings
     */
    public function bindValues($statement, $bindings)
    {
        foreach ($bindings as $key => $value) {
            $statement->bindValue(
                is_string($key) ? $key : $key + 1, $value,
                is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR
            );
        }
    }

    /**
     * 统一的sql执行方法
     * @param $query
     * @param $bindings
     * @param Closure $callback
     * @return mixed
     */
    protected function run($query, $bindings, Closure $callback)
    {
        $this->reconnectIfMissingConnection();
        $start = microtime(true);
        try {
            $result = $this->runQueryCallback($query, $bindings, $callback);
        } catch (QueryException $e) {
            $result = $this->handleQueryException($e, $query, $bindings, $callback);
        }
        $this->logQuery($query, $bindings, $this->getElapsedTime($start));
        return $result;
    }

    /**
     * 查询回调
     * @param $query
     * @param $bindings
     * @param Closure $callback
     * @return mixed
     */
    protected function runQueryCallback($query, $bindings, Closure $callback)
    {
        try {
            $result = $callback($query, $bindings);
        } catch (Exception $e) {
            throw new QueryException($query, $this->prepareBindings($bindings), $e);
        }
        return $result;
    }

    /**
     * 记录query日志
     * @param $query
     * @param $bindings
     * @param null $time
     */
    public function logQuery($query, $bindings, $time = null)
    {
        if ($this->loggingQueries) {
            $this->queryLog[] = compact('query', 'bindings', 'time');
        }
    }

    /**
     * 设置PDO的fetch_mode
     *
     * @param  \PDOStatement  $statement
     * @return \PDOStatement
     */
    protected function prepared(PDOStatement $statement)
    {
        $statement->setFetchMode($this->fetchMode);
        return $statement;
    }

    /**
     * 返回一个select的迭代器
     * @param $query
     * @param array $bindings
     * @param bool $useReadPdo
     * @return \Generator
     */
    public function cursor($query, $bindings = [], $useReadPdo = true)
    {
        $statement = $this->run($query, $bindings, function ($query, $bindings) use ($useReadPdo) {
            if ($this->pretending()) {
                return [];
            }
            $statement = $this->prepared($this->getPdoForSelect($useReadPdo)->prepare($query));
            $this->bindValues($statement, $this->prepareBindings($bindings));
            $statement->execute();
            return $statement;
        });

        while ($record = $statement->fetch()) {
            yield $record;
        }
    }

    protected function getPdoForSelect($useReadPdo = true)
    {
        return $useReadPdo ? $this->getReadPdo() : $this->getPdo();
    }

    /**
     * insert语句
     * @param $query
     * @param array $bindings
     * @return bool
     */
    public function insert($query, $bindings = [])
    {
        return $this->statement($query, $bindings);
    }

    /**
     * update语句
     * @param $query
     * @param array $bindings
     * @return int
     */
    public function update($query, $bindings = [])
    {
        return $this->affectingStatement($query, $bindings);
    }

    /**
     * delete语句
     * @param $query
     * @param array $bindings
     * @return int
     */
    public function delete($query, $bindings = [])
    {
        return $this->affectingStatement($query, $bindings);
    }

    /**
     * 执行一个sql语句，返回bool值
     *
     * @param  string  $query
     * @param  array   $bindings
     * @return bool
     */
    public function statement($query, $bindings = [])
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            if ($this->pretending()) {
                return true;
            }
            $statement = $this->getPdo()->prepare($query);
            $this->bindValues($statement, $this->prepareBindings($bindings));
            return $statement->execute();
        });
    }

    /**
     * 运行sql，返回影响行数
     * @param $query
     * @param array $bindings
     * @return mixed
     */
    public function affectingStatement($query, $bindings = [])
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            if ($this->pretending()) {
                return 0;
            }
            $statement = $this->getPdo()->prepare($query);
            $this->bindValues($statement, $this->prepareBindings($bindings));
            $statement->execute();
            return $statement->rowCount();
        });
    }

    /**
     * 直接运行sql，不做任何安全处理
     * @param $query
     * @return mixed
     */
    public function unprepared($query)
    {
        return $this->run($query, [], function ($query) {
            if ($this->pretending()) {
                return true;
            }
            return $this->getPdo()->exec($query) === false ? false : true;
        });
    }

    public function pretend(Closure $callback)
    {
        return $this->withFreshQueryLog(function () use ($callback) {
            $this->pretending = true;
            $callback($this);
            $this->pretending = false;
            return $this->queryLog;
        });
    }

    protected function withFreshQueryLog($callback)
    {
        $loggingQueries = $this->loggingQueries;
        $this->enableQueryLog();
        $this->queryLog = [];
        $result = $callback();
        $this->loggingQueries = $loggingQueries;
        return $result;
    }

    public function logging()
    {
        return $this->loggingQueries;
    }

    protected function getElapsedTime($start)
    {
        return round((microtime(true) - $start) * 1000, 2);
    }

    protected function handleQueryException($e, $query, $bindings, Closure $callback)
    {
        if ($this->transactions >= 1) {
            throw $e;
        }
        return $this->tryAgainIfCausedByLostConnection($e, $query, $bindings, $callback);
    }

    protected function tryAgainIfCausedByLostConnection(QueryException $e, $query, $bindings, Closure $callback)
    {
        if ($this->causedByLostConnection($e->getPrevious())) {
            $this->reconnect();
            return $this->runQueryCallback($query, $bindings, $callback);
        }
        throw $e;
    }

    /**
     * 重新连接
     * @return mixed
     */
    public function reconnect()
    {
        if (is_callable($this->reconnector)) {
            return call_user_func($this->reconnector, $this);
        }
        throw new LogicException('Lost connection and no reconnector available.');
    }

    /**
     * 如果连接不存在则进行重连
     */
    protected function reconnectIfMissingConnection()
    {
        if (is_null($this->pdo)) {
            $this->reconnect();
        }
    }

    /**
     * 断开连接
     *
     * @return void
     */
    public function disconnect()
    {
        $this->setPdo(null)->setReadPdo(null);
    }

    /**
     * Is Doctrine available?
     *
     * @return bool
     */
    public function isDoctrineAvailable()
    {
        return class_exists('Doctrine\DBAL\Connection');
    }

    /**
     * 获取父作用域的pdo
     * @return mixed
     */
    public function getPdo()
    {
        if ($this->pdo instanceof Closure) {
            return $this->pdo = call_user_func($this->pdo);
        }
        return $this->pdo;
    }

    public function getReadPdo()
    {
        if ($this->transactions >= 1) {
            return $this->getPdo();
        }
        if ($this->readPdo instanceof Closure) {
            return $this->readPdo = call_user_func($this->readPdo);
        }
        return $this->readPdo ?: $this->getPdo();
    }

    /**
     * 设置pdo
     * @param $pdo
     * @return $this
     */
    public function setPdo($pdo)
    {
        $this->transactions = 0;
        $this->pdo = $pdo;
        return $this;
    }

    /**
     * 设置读PDO
     * @param $pdo
     * @return $this
     */
    public function setReadPdo($pdo)
    {
        $this->readPdo = $pdo;
        return $this;
    }

    public function setReconnector(callable $reconnector)
    {
        $this->reconnector = $reconnector;
        return $this;
    }

    public function getName()
    {
        return $this->getConfig('name');
    }

    public function getConfig($option = null)
    {
        return Arr::get($this->config, $option);
    }

    public function getDriverName()
    {
        return $this->getConfig('driver');
    }

    public function pretending()
    {
        return $this->pretending === true;
    }

    public function getQueryLog()
    {
        return $this->queryLog;
    }

    public function flushQueryLog()
    {
        $this->queryLog = [];
    }

    public function enableQueryLog()
    {
        $this->loggingQueries = true;
    }

    public function disableQueryLog()
    {
        $this->loggingQueries = false;
    }

    public function getDatabaseName()
    {
        return $this->database;
    }

    public function setDatabaseName($database)
    {
        $this->database = $database;
    }

    public function getTablePrefix()
    {
        return $this->tablePrefix;
    }

    public static function getResolver($driver)
    {
        return isset(static::$resolvers[$driver]) ? static::$resolvers[$driver] : null;
    }

    public function table($table)
    {
    }


    public function raw($value)
    {
    }
}
