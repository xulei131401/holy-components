<?php

namespace Holy\Components\Database\Factory;

use PDO;

class MySqlConnection extends Connection
{
    /**
     * mysql驱动绑定参数
     * @param $statement
     * @param $bindings
     */
    public function bindValues($statement, $bindings)
    {
        foreach ($bindings as $key => $value) {
            $statement->bindValue(is_string($key) ? $key : $key + 1, $value, is_int($value) || is_float($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
    }
}
