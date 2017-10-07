<?php

namespace Holy\Components\Hashing;

use RuntimeException;
use Holy\Components\Contracts\Hashing\Hasher as HasherContract;

class BcryptHasher implements HasherContract
{
    protected $cost = 10;

    /**
     * 进行密码hash
     * @param string $value
     * @param array $options
     * @return bool|string
     */
    public function make($value, array $options = [])
    {
        $cost = isset($options['cost']) ? $options['cost'] : $this->cost;

        $hash = password_hash($value, PASSWORD_BCRYPT, ['cost' => $cost]);

        if ($hash === false) {
            throw new RuntimeException('Bcrypt hashing not supported.');
        }

        return $hash;
    }

    /**
     * 验证hash
     *
     * @param  string  $value
     * @param  string  $hashedValue
     * @param  array   $options
     * @return bool
     */
    public function check($value, $hashedValue, array $options = [])
    {
        if (strlen($hashedValue) === 0) {
            return false;
        }

        return password_verify($value, $hashedValue);
    }

    /**
     * 重新hash
     * @param string $hashedValue
     * @param array $options
     * @return bool
     */
    public function needsRehash($hashedValue, array $options = [])
    {
        return password_needs_rehash($hashedValue, PASSWORD_BCRYPT, [
            'cost' => isset($options['cost']) ? $options['cost'] : $this->cost,
        ]);
    }

    /**
     * 设置默认的算法cost，一般是8-10
     * @param $cost
     * @return $this
     */
    public function setCost($cost)
    {
        $this->cost = (int) $cost;
        return $this;
    }
}
