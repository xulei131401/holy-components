<?php

namespace Holy\Components\Database\Exception;

use Holy\Components\Primary\Str;
use PDOException;

class QueryException extends PDOException
{
    protected $sql;

    protected $bindings;

    public function __construct($sql, array $bindings, $previous)
    {
        parent::__construct('', 0, $previous);

        $this->sql = $sql;
        $this->bindings = $bindings;
        $this->code = $previous->getCode();
        $this->message = $this->formatMessage($sql, $bindings, $previous);

        if ($previous instanceof PDOException) {
            $this->errorInfo = $previous->errorInfo;
        }
    }

    /**
     * Format the SQL error message.
     *
     * @param  string  $sql
     * @param  array  $bindings
     * @param  \Exception $previous
     * @return string
     */
    protected function formatMessage($sql, $bindings, $previous)
    {
        return $previous->getMessage().' (SQL: '.Str::replaceArray('?', $bindings, $sql).')';
    }

    /**
     * Get the SQL for the query.
     *
     * @return string
     */
    public function getSql()
    {
        return $this->sql;
    }

    /**
     * Get the bindings for the query.
     *
     * @return array
     */
    public function getBindings()
    {
        return $this->bindings;
    }
}
