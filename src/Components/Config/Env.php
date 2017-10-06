<?php

namespace Holy\Components\Config;

class Env
{
    /**
     * 以面向对象的方式提供一个ENV变量的访问接口
     * @param $name
     * @param null $default
     * @return array|bool|false|mixed|null|string
     */
    public static function get($name, $default = null)
    {
        return env($name, $default);
    }
}
