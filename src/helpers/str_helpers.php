<?php
use Holy\Support\Str;

/*以下是字符串全局函数*/
if (! function_exists('str_random')) {
    
    function str_random($length)
    {
        return Str::random($length);
    }
}

if (! function_exists('quick_random')) {
    
    function quick_random($length)
    {
        return Str::quickRandom($length);
    }
}

if (! function_exists('str_limit')) {
    
    function str_limit($value, $limit = 100, $end)
    {
        return Str::limit($value, $limit, $end);
    }
}

if (! function_exists('starts_with')) {
    
    function starts_with($haystack, $needles)
    {
        return Str::startsWith($haystack, $needles);
    }
}

if (! function_exists('str_finish')) {
    
    function str_finish($value, $cap)
    {
        return Str::finish($value, $cap);
    }
}

if (! function_exists('ends_with')) {
    
    function ends_with($haystack, $needles)
    {
        return Str::endsWith($haystack, $needles);
    }
}

if (! function_exists('str_contains')) {
    
    function str_contains($haystack, $needles)
    {
        return Str::contains($haystack, $needles);
    }
}

if (! function_exists('str_is')) {
    
    function str_is($pattern, $value)
    {
        return Str::is($pattern, $value);
    }
}

if (! function_exists('studly_case')) {
    
    function studly_case($value)
    {
        return Str::studly($value);
    }
}

/* End of file str_helpers.php */