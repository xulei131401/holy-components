<?php
use Holy\Components\Primary\Str;

/*以下是字符串全局函数*/
if (! function_exists('str_random')) {
    
    function str_random($length = 16)
    {
        return Str::random($length);
    }
}

if (! function_exists('quick_random')) {
    
    function quick_random($length = 16, $type = null)
    {
        return Str::quickRandom($length, $type);
    }
}

if (! function_exists('is_utf8')) {

    function is_utf8($string = '')
    {
        return Str::isUTF8($string);
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

if (! function_exists('str_replace_last')) {

    function str_replace_last($search, $replace, $subject)
    {
        return Str::replaceLast($search, $replace, $subject);
    }
}

if (! function_exists('str_replace_first')) {

    function str_replace_first($search, $replace, $subject)
    {
        return Str::replaceFirst($search, $replace, $subject);
    }
}

if (! function_exists('str_replace_array')) {

    function str_replace_array($search, array $replace, $subject)
    {
        return Str::replaceArray($search, $replace, $subject);
    }
}

if (! function_exists('title_case')) {

    function title_case($value)
    {
        return Str::title($value);
    }
}

if (! function_exists('str_slug')) {

    function str_slug($title, $separator = '-')
    {
        return Str::slug($title, $separator);
    }
}

if (! function_exists('camel_case')) {
    /**
     * Convert a value to camel case.
     *
     * @param  string  $value
     * @return string
     */
    function camel_case($value)
    {
        return Str::camel($value);
    }
}

if (! function_exists('snake_case')) {
    /**
     * Convert a string to snake case.
     *
     * @param  string  $value
     * @param  string  $delimiter
     * @return string
     */
    function snake_case($value, $delimiter = '_')
    {
        return Str::snake($value, $delimiter);
    }
}
/* End of file str_helpers.php */