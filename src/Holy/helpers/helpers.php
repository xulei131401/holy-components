<?php
/*下面是全局普通方法*/
use Holy\Foundation\Support\Str;

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

        if (strlen($value) > 1 && Str::startsWith($value, '"') && Str::endsWith($value, '"')) {
            return substr($value, 1, -1);
        }

        return $value;
    }
}

/* End of file helpers.php */