<?php

namespace Holy\Components\Database\Factory;

use Closure;

interface ConnectionInterface
{
    public function table($table);

    public function raw($value);

    public function selectOne($query, $bindings = []);

    public function select($query, $bindings = []);

    public function insert($query, $bindings = []);

    public function update($query, $bindings = []);

    public function delete($query, $bindings = []);

    public function statement($query, $bindings = []);

    public function affectingStatement($query, $bindings = []);

    public function unprepared($query);

    public function prepareBindings(array $bindings);

    public function transaction(Closure $callback, $attempts = 1);

    public function beginTransaction();

    public function commit();

    public function rollBack();

    public function transactionLevel();

    public function pretend(Closure $callback);
}
