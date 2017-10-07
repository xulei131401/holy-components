<?php
/*下面是全局普通方法*/

use Holy\Components\Contracts\Support\Htmlable;
use Holy\Components\Primary\Str;
use Holy\Components\Support\Debug\Dumper;
use Holy\Components\Hashing\BcryptHasher;

if (! function_exists('head')) {
    
    function head($array)
    {
        return reset($array);
    }
}

if (! function_exists('last')) {
    
    function last($array)
    {
        return end($array);
    }
}

if (! function_exists('isWindowsOs')) {
    
    function isWindowsOs()
    {
        return strtolower(substr(PHP_OS, 0, 3)) === 'win';
    }
}

if (! function_exists('class_basename')) {
    
    /*可以传入完整命名空间字符串，也可以传入一个对象, 当传入错误参数后会返回不可预知的错误结果！*/
    function class_basename($class)
    {
        $class = is_object($class) ? get_class($class) : $class;

        return basename(str_replace('\\', '/', $class));
    }
}

if (! function_exists('retry')) {
    /*retry 函数尝试执行给定回调直到达到最大执行次数，如果回调没有抛出异常，会返回对应的返回值。如果回调抛出了异常，会自动重试。如果超出最大执行次数，异常会被抛出*/
    function retry($times, callable $callback, $sleep = 0)
    {
        $times--;

        beginning:
        try {
            return $callback();
        } catch (Exception $e) {
            if (! $times) {
                throw $e;
            }

            $times--;

            if ($sleep) {
                usleep($sleep * 1000);
            }

            goto beginning;
        }
    }
}

if (! function_exists('value')) {
    /*value 函数返回给定的值，然而，如果你传递一个闭包到该函数，该闭包将会被执行并返回执行结果：*/
    function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}

if (! function_exists('with')) {
   /*with 函数返回给定的值，该函数在方法链中特别有用,$value = with(new Foo)->work();*/
    function with($object)
    {
        return $object;
    }
}

if (! function_exists('env')) {
    /**
     * 支持env文件各种值，true,false,null,empty以及字符串
     * @param $key
     * @param null $default
     * @return array|bool|false|mixed|null|string
     */
    function env($key, $default = null)
    {
        $value = getenv($key);

        if ($value === false) {
            return value($default);
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return null;
        }
        // 用于处理特殊字符
        if (strlen($value) > 1 && Str::startsWith($value, '"') && Str::endsWith($value, '"')) {
            return substr($value, 1, -1);
        }
        return $value;
    }
}

/**
 * 便捷的打印函数
 */
if (! function_exists('dd')) {

    function dd()
    {
        array_map(function ($x) {(new Dumper)->dump($x);}, func_get_args());
        die(1);
    }
}

if (! function_exists('e')) {
    /**
     * htmlspecialchars() 函数把预定义的字符转换为 HTML 实体。
        预定义的字符是：
        & （和号）成为 &
        " （双引号）成为 "
        ' （单引号）成为 '
        < （小于）成为 <
        > （大于）成为 >
     * @param $value
     * @return string
     */
    function e($value)
    {
        if ($value instanceof Htmlable) {
            return $value->toHtml();
        }
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8', false);
    }
}

if (! function_exists('object_get')) {

    function object_get($object, $key, $default = null)
    {
        if (is_null($key) || trim($key) == '') {
            return $object;
        }

        foreach (explode('.', $key) as $segment) {
            if (! is_object($object) || ! isset($object->{$segment})) {
                return value($default);
            }

            $object = $object->{$segment};
        }

        return $object;
    }
}

if (! function_exists('trait_uses_recursive')) {

    function trait_uses_recursive($trait)
    {
        $traits = class_uses($trait);

        foreach ($traits as $trait) {
            $traits += trait_uses_recursive($trait);
        }

        return $traits;
    }
}

if (! function_exists('tap')) {
    /**
     * 为当前值执行一个回调
     * @param $value
     * @param $callback
     * @return mixed
     */
    function tap($value, $callback)
    {
        $callback($value);

        return $value;
    }
}

if (! function_exists('preg_replace_array')) {

    function preg_replace_array($pattern, array $replacements, $subject)
    {
        return preg_replace_callback($pattern, function () use (&$replacements) {
            foreach ($replacements as $key => $value) {
                return array_shift($replacements);
            }
        }, $subject);
    }
}

if (! function_exists('bcrypt')) {

    function bcrypt($value, $options = [])
    {
        return with(new BcryptHasher())->make($value, $options);
    }
}

if (! function_exists('__include_file')) {

    function __include_file($file)
    {
        return include $file;
    }
}

if (! function_exists('__include_file')) {

    function __require_file($file)
    {
        return require $file;
    }
}

/* End of file helpers.php */