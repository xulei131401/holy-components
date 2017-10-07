<?php
namespace Holy\Components\Encryption;

use Exception;
use RuntimeException;
use Holy\Components\Contracts\Encryption\Encrypter as EncrypterContract;

class Encrypter implements EncrypterContract
{
    protected $key;
    protected $cipher;

    public function __construct($key, $cipher = 'AES-128-CBC')
    {
        $key = (string) $key;

        if (static::supported($key, $cipher)) {
            $this->key = $key;
            $this->cipher = $cipher;
        } else {
            throw new RuntimeException('The only supported ciphers are AES-128-CBC and AES-256-CBC with the correct key lengths.');
        }
    }

    /**
     * 确定给定的密钥和密码组合是否有效
     *
     * @param  string  $key
     * @param  string  $cipher
     * @return bool
     */
    public static function supported($key, $cipher)
    {
        $length = mb_strlen($key, '8bit');

        return ($cipher === 'AES-128-CBC' && $length === 16) ||
               ($cipher === 'AES-256-CBC' && $length === 32);
    }

    /**
     * 加密
     * @param $value
     * @param bool $serialize
     * @return string
     * @throws Exception
     */
    public function encrypt($value, $serialize = true)
    {
        $iv = random_bytes(16);

        $value = \openssl_encrypt(
            $serialize ? serialize($value) : $value,
            $this->cipher, $this->key, 0, $iv
        );

        if ($value === false) {
            throw new Exception('Could not encrypt the data.');
        }

        $mac = $this->hash($iv = base64_encode($iv), $value);

        $json = json_encode(compact('iv', 'value', 'mac'));

        if (! is_string($json)) {
            throw new Exception('Could not encrypt the data.');
        }

        return base64_encode($json);
    }

    /**
     * 无序列化加密
     *
     * @param  string  $value
     * @return string
     */
    public function encryptString($value)
    {
        return $this->encrypt($value, false);
    }

    /**
     * 解密
     * @param $payload
     * @param bool $unserialize
     * @return mixed|string
     * @throws Exception
     */
    public function decrypt($payload, $unserialize = true)
    {
        $payload = $this->getJsonPayload($payload);

        $iv = base64_decode($payload['iv']);

        $decrypted = \openssl_decrypt(
            $payload['value'], $this->cipher, $this->key, 0, $iv
        );

        if ($decrypted === false) {
            throw new Exception('Could not decrypt the data.');
        }

        return $unserialize ? unserialize($decrypted) : $decrypted;
    }

    /**
     * 无序列化解密.
     *
     * @param  string  $payload
     * @return string
     */
    public function decryptString($payload)
    {
        return $this->decrypt($payload, false);
    }

    /**
     * Create a MAC for the given value.
     *
     * @param  string  $iv
     * @param  mixed  $value
     * @return string
     */
    protected function hash($iv, $value)
    {
        return hash_hmac('sha256', $iv.$value, $this->key);
    }

    /**
     * @param $payload
     * @return mixed
     * @throws Exception
     */
    protected function getJsonPayload($payload)
    {
        $payload = json_decode(base64_decode($payload), true);

        if (! $this->validPayload($payload)) {
            throw new Exception('The payload is invalid.');
        }

        if (! $this->validMac($payload)) {
            throw new Exception('The MAC is invalid.');
        }

        return $payload;
    }

    /**
     * Verify that the encryption payload is valid.
     *
     * @param  mixed  $payload
     * @return bool
     */
    protected function validPayload($payload)
    {
        return is_array($payload) && isset(
            $payload['iv'], $payload['value'], $payload['mac']
        );
    }

    /**
     * Determine if the MAC for the given payload is valid.
     *
     * @param  array  $payload
     * @return bool
     */
    protected function validMac(array $payload)
    {
        $calculated = $this->calculateMac($payload, $bytes = random_bytes(16));

        return hash_equals(
            hash_hmac('sha256', $payload['mac'], $bytes, true), $calculated
        );
    }

    /**
     * Calculate the hash of the given payload.
     *
     * @param  array  $payload
     * @param  string  $bytes
     * @return string
     */
    protected function calculateMac($payload, $bytes)
    {
        return hash_hmac(
            'sha256', $this->hash($payload['iv'], $payload['value']), $bytes, true
        );
    }

    /**
     * 获取加密的key值.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }
}

/* End of file Encrypter.php */
