<?php

namespace Holy\Components\Support\Traits;

use Closure;
use BadMethodCallException;

/**
 * 动态的将callable类型的参数供对象调用
 * Trait Macroable
 * @package Holy\Components\Support\Traits
 */
trait Macroable
{

    protected static $macros = [];

    /**
     * 为对象添加方法
     * @param $name
     * @param callable $macro
     */
    public static function addMethod($name, callable $macro)
    {
        static::$macros[$name] = $macro;
    }

    /**
     * 判断是否添加过该方法
     * @param $name
     * @return bool
     */
    public static function hasMacro($name)
    {
        return isset(static::$macros[$name]);
    }

    /**
     * 静态调用方法
     * @param $method
     * @param $parameters
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        if (! static::hasMacro($method)) {
            throw new BadMethodCallException("Method {$method} does not exist.");
        }

        if (static::$macros[$method] instanceof Closure) {
            return call_user_func_array(Closure::bind(static::$macros[$method], null, static::class), $parameters);
        }

        return call_user_func_array(static::$macros[$method], $parameters);
    }

    /**
     * 普通方法调用
     * @param $method
     * @param $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (! static::hasMacro($method)) {
            throw new BadMethodCallException("Method {$method} does not exist.");
        }

        if (static::$macros[$method] instanceof Closure) {
            return call_user_func_array(static::$macros[$method]->bindTo($this, static::class), $parameters);
        }

        return call_user_func_array(static::$macros[$method], $parameters);
    }
}
