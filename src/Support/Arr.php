<?php 
namespace Holy\Support;

use ArrayAccess;
class Arr
{
	/**
     * [判断是否可以以数组形式访问]
     * @param  [type]  $value [description]
     * @return boolean        [description]
     */
    public static function isAccessible($value)
    {
        return is_array($value) || $value instanceof ArrayAccess;
    }

     /**
      * [判断值是否存在]
      * @param  [type]  $array [description]
      * @param  [type]  $key   [description]
      * @return boolean        [description]
      */
    public static function isExists($array, $key)
    {
        if ($array instanceof ArrayAccess) {
            return $array->offsetExists($key);
        }

        return array_key_exists($key, $array);
    }

    /**
     * [获取值]
     * @param  [type] $array   [description]
     * @param  [type] $key     [description]
     * @param  [type] $default [description]
     * @return [type]          [description]
     */
    public static function get($array, $key, $default = null)
    {
        if (! static::isAccessible($array)) {
            return value($default);
        }

        if (is_null($key)) {
            return $array;
        }

        if (static::isExists($array, $key)) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (static::isAccessible($array) && static::isExists($array, $segment)) {
                $array = $array[$segment];
            } else {
                return value($default);
            }
        }

        return $array;
    }

    /**
     * [设置值]
     * @param [type] &$array [description]
     * @param [type] $key    [description]
     * @param [type] $value  [description]
     */
    public static function set(&$array, $key, $value)
    {
        if (is_null($key)) {
            return $array = $value;
        }

        $keys = explode('.', $key);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if (! isset($array[$key]) || ! is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }

	/**
     * [往数组中添加值]
     * @param [type] $array [description]
     * @param [type] $key   [description]
     * @param [type] $value [description]
     */
    public static function add($array, $key, $value)
    {
        if (is_null(static::get($array, $key))) {
            static::set($array, $key, $value);
        }

        return $array;
    }

    /**
     * [拆分数组为两部分，一部分只有键，一部分只有值]
     * @param  [type] $array [description]
     * @return [type]        [description]
     */
    public static function divide($array)
    {
        return [array_keys($array), array_values($array)];
    }

    /**
     * [利用.语法将多维数组合并为一维数组]
     * @param  [type] $array   [description]
     * @param  string $prepend [附加的前缀]
     * @return [type]          [description]
     */
    public static function dot($array, $prepend = '')
    {
        $results = [];

        foreach ($array as $key => $value) {
            if (is_array($value) && ! empty($value)) {
                $results = array_merge($results, static::dot($value, $prepend.$key.'.'));
            } else {
                $results[$prepend.$key] = $value;
            }
        }

        return $results;
    }

    /**
     * Get all of the given array except for a specified array of items.
     *
     * @param  array  $array
     * @param  array|string  $keys
     * @return array
     */
    public static function except($array, $keys)
    {
        static::forget($array, $keys);

        return $array;
    }

    /**
     * [这个方法不好，重写]
     * @param  [type] &$array [description]
     * @param  [type] $keys   [description]
     * @return [type]         [description]
     */
    public static function forget(&$array, $keys)
    {
        $original = &$array;
        $keys = (array) $keys;

        if (count($keys) === 0) {
            return;
        }
        foreach ($keys as $key) {
            if (static::isExists($array, $key)) {
                unset($array[$key]);
                continue;
            }
            $parts = explode('.', $key);
            $array = &$original;
            while (count($parts) > 1) {
                $part = array_shift($parts);
                if (isset($array[$part]) && is_array($array[$part])) {
                    $array = &$array[$part];
                } else {
                    continue 2;
                }
            }
            unset($array[array_shift($parts)]);
        }
    }

    /**
     * [返回符合callable函数的第一个元素，与array_filter的区别在于filter返回一个数组]
     * @param  [type]        $array    [description]
     * @param  callable|null $callback [description]
     * @param  [type]        $default  [description]
     * @return [type]                  [description]
     */
    public static function first($array, callable $callback = null, $default = null)
    {
        if (is_null($callback)) {
            if (empty($array)) {
                return value($default);
            }

            foreach ($array as $item) {
                return $item;
            }
        }

        foreach ($array as $key => $value) {
            if (call_user_func($callback, $value, $key)) {
                return $value;
            }
        }

        return value($default);
    }

    /**
     * [将多维数组转换位一维数组](与array_dot还是有所区别的)
     * @param  [type]  $array [description]
     * @param  integer $depth [description]
     * @return [type]         [description]
     */
    public static function flatten($array, $depth = INF)
    {
        return array_reduce($array, function ($result, $item) use ($depth) {
            $item = $item instanceof Collection ? $item->all() : $item;

            if (! is_array($item)) {
                return array_merge($result, [$item]);
            } elseif ($depth === 1) {
                return array_merge($result, array_values($item));
            } else {
                return array_merge($result, static::flatten($item, $depth - 1));
            }
        }, []);
    }

    /**
     * [使用.语法检查给定项是否在数组中存在]
     * @param  [type]  $array [description]
     * @param  [type]  $keys  [description]
     * @return boolean        [description]
     */
    public static function has($array, $keys)
    {
        if (is_null($keys)) {
            return false;
        }

        $keys = (array) $keys;

        if (! $array) {
            return false;
        }

        if ($keys === []) {
            return false;
        }

        foreach ($keys as $key) {
            $subKeyArray = $array;

            if (static::isExists($array, $key)) {
                continue;
            }

            foreach (explode('.', $key) as $segment) {
                if (static::isAccessible($subKeyArray) && static::isExists($subKeyArray, $segment)) {
                    $subKeyArray = $subKeyArray[$segment];
                } else {
                    return false;
                }
            }
        }

        return true;
    }

   /**
    * [last description]
    * @param  [type]        $array    [description]
    * @param  callable|null $callback [description]
    * @param  [type]        $default  [description]
    * @return [type]                  [description]
    */
    public static function last($array, callable $callback = null, $default = null)
    {
        if (is_null($callback)) {
            return empty($array) ? value($default) : end($array);
        }

        return static::first(array_reverse($array, true), $callback, $default);
    }

    /**
     * [返回数组中指定的键值对]
     * @param  [type] $array [description]
     * @param  [type] $keys  [description]
     * @return [type]        [description]
     */
    public static function only($array, $keys)
    {
        return array_intersect_key($array, array_flip((array) $keys));
    }

     /**
      * [pluck 方法待定]
      * @param  [type] $array [description]
      * @param  [type] $value [description]
      * @param  [type] $key   [description]
      * @return [type]        [description]
      */
    public static function pluck($array, $value, $key = null)
    {
        $results = [];

        list($value, $key) = static::explodePluckParameters($value, $key);

        foreach ($array as $item) {
            $itemValue = data_get($item, $value);

            // If the key is "null", we will just append the value to the array and keep
            // looping. Otherwise we will key the array using the value of the key we
            // received from the developer. Then we'll return the final array form.
            if (is_null($key)) {
                $results[] = $itemValue;
            } else {
                $itemKey = data_get($item, $key);

                $results[$itemKey] = $itemValue;
            }
        }

        return $results;
    }

    /**
     * [explodePluckParameters description]
     * @param  [type] $value [description]
     * @param  [type] $key   [description]
     * @return [type]        [description]
     */
    protected static function explodePluckParameters($value, $key)
    {
        $value = is_string($value) ? explode('.', $value) : $value;

        $key = is_null($key) || is_array($key) ? $key : explode('.', $key);

        return [$value, $key];
    }

    /**
     * [向数组的开头插入键值对]
     * @param  [type] $array [description]
     * @param  [type] $value [description]
     * @param  [type] $key   [description]
     * @return [type]        [description]
     */
    public static function prepend($array, $value, $key = null)
    {
        if (is_null($key)) {
            array_unshift($array, $value);
        } else {
            $array = [$key => $value] + $array;
        }

        return $array;
    }

    /**
     * [返回移除的数组中的指定键值对]
     * @param  [type] &$array  [description]
     * @param  [type] $key     [description]
     * @param  [type] $default [description]
     * @return [type]          [description]
     */
    public static function pull(&$array, $key, $default = null)
    {
        $value = static::get($array, $key, $default);

        static::forget($array, $key);

        return $value;
    }

    /**
     * [where description]
     * @param  [type]   $array    [description]
     * @param  callable $callback [description]
     * @return [type]             [description]
     */
    public static function where($array, callable $callback)
    {
        /**
         * 决定callback接收的参数形式: 
            •ARRAY_FILTER_USE_KEY - callback接受键名作为的唯一参数
            •ARRAY_FILTER_USE_BOTH - callback同时接受键名和键值 
         */
        return array_filter($array, $callback, ARRAY_FILTER_USE_BOTH);
    }

}