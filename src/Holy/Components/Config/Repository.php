<?php

namespace Holy\Config;

use ArrayAccess;
use Holy\Components\Foundation\Arr;
use Holy\Contracts\Config\Repository as ConfigContract;

class Repository implements ArrayAccess, ConfigContract
{

    protected $items = [];

    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    function has($key)
    {
        return Arr::has($this->items, $key);
    }

    public function get($key, $default = null)
    {
        return Arr::get($this->items, $key, $default);
    }

    public function set($key, $value = null)
    {
        $keys = is_array($key) ? $key : [$key => $value];

        foreach ($keys as $key => $value) {
            Arr::set($this->items, $key, $value);
        }
    }

    public function prepend($key, $value)
    {
        $array = $this->get($key);

        array_unshift($array, $value);

        $this->set($key, $array);
    }

    public function push($key, $value)
    {
        $array = $this->get($key);

        $array[] = $value;

        $this->set($key, $array);
    }

    public function all()
    {
        return $this->items;
    }

/*ArrayAccess接口的必须实现方法*/
    public function offsetExists($key)
    {
        return $this->has($key);
    }

    public function offsetGet($key)
    {
        return $this->get($key);
    }

    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    public function offsetUnset($key)
    {
        $this->set($key, null);
    }
}
