<?php
use Holy\Components\Arr;

/*下面是暴露的Arr的静态方法*/
if (! function_exists('array_add')) {
    
    function array_add($array, $key, $value)
    {
        return Arr::add($array, $key, $value);
    }
}

if (! function_exists('array_divide')) {
    
    function array_divide($array)
    {
        return Arr::divide($array);
    }
}

if (! function_exists('array_dot')) {
    
    function array_dot($array, $prepend = '')
    {
        return Arr::dot($array, $prepend);
    }
}

if (! function_exists('array_except')) {
    
    function array_except($array, $keys)
    {
        return Arr::except($array, $keys);
    }
}

if (! function_exists('array_first')) {

    function array_first($array, callable $callback = null, $default = null)
    {
        return Arr::first($array, $callback, $default);
    }
}

if (! function_exists('array_last')) {
    
    function array_last($array, callable $callback = null, $default = null)
    {
        return Arr::last($array, $callback, $default);
    }
}

if (! function_exists('array_flatten')) {
    
    function array_flatten($array, $depth = INF)
    {
        return Arr::flatten($array, $depth);
    }
}

if (! function_exists('array_forget')) {
   
    function array_forget(&$array, $keys)
    {
        return Arr::forget($array, $keys);
    }
}

if (! function_exists('array_get')) {
    
    function array_get($array, $key, $default = null)
    {
        return Arr::get($array, $key, $default);
    }
}

if (! function_exists('array_has')) {
    
    function array_has($array, $keys)
    {
        return Arr::has($array, $keys);
    }
}

if (! function_exists('array_only')) {
    
    function array_only($array, $keys)
    {
        return Arr::only($array, $keys);
    }
}

if (! function_exists('array_pluck')) {
    
    function array_pluck($array, $value, $key = null)
    {
        return Arr::pluck($array, $value, $key);
    }
}

if (! function_exists('array_prepend')) {
    
    function array_prepend($array, $value, $key = null)
    {
        return Arr::prepend($array, $value, $key);
    }
}

if (! function_exists('array_pull')) {
   
    function array_pull(&$array, $key, $default = null)
    {
        return Arr::pull($array, $key, $default);
    }
}

if (! function_exists('array_set')) {
    
    function array_set(&$array, $key, $value)
    {
        return Arr::set($array, $key, $value);
    }
}

if (! function_exists('array_where')) {
    
    function array_where($array, callable $callback)
    {
        return Arr::where($array, $callback);
    }
}

/* End of file arr_helpers.php */