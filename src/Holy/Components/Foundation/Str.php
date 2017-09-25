<?php 
namespace Holy\Components\Foundation;

class Str
{
    protected static $randomFactor = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    protected static $studlyCache = [];

    /**
     * 获取指定长度的随机字符串:实现方式通过生成随机字节进行截取
     * @param int $length
     * @return string
     */
    public static function random($length = 16)
    {
        $string = '';
        if ((int) $length > 0) {
            $bytes = random_bytes($length);	//此函数用于产生安全的伪随机字节（php7函数的pollyfill）
            $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $length);
        }
        return $string;
    }

    /**
     * 快速随机生成只有字母数字的字符串，针对7以下版本走常规方法生成，7以上版本使用random_bytes生成
     * @param int $length
     * @return bool|string
     */
    public static function quickRandom($length = 16)
    {
        if (PHP_MAJOR_VERSION > 5) {
            return static::random($length);
        }
        return substr(str_shuffle(str_repeat(static::$randomFactor, $length)), 0, $length);
    }

    /**
     * 截取指定长度字符串,注意mb_strwidth的使用
     * @param $value
     * @param int $limit
     * @param string $end
     * @return string
     */
    public static function limit($value, $limit = 100, $end = '')
    {
        if (mb_strwidth($value, 'UTF-8') <= $limit) {
            return $value;
        }

        return rtrim(mb_strimwidth($value, 0, $limit, '', 'UTF-8')).$end;
    }

    /**
     * 判断字符串是不是已指定字符串开头，指定的字符串可以是字符串类型也可以是数组类型
     * @param $haystack
     * @param $needles
     * @return bool
     */
    public static function startsWith($haystack, $needles)
    {
        foreach ((array) $needles as $needle) {
            if ($needle != '' && substr($haystack, 0, strlen($needle)) === (string) $needle) {
                return true;
            }
        }

        return false;
    }

    /**
     * 将指定字符添加到字符串末尾
     * @param $value
     * @param $cap
     * @return string
     */
    public static function finish($value, $cap)
    {
        $quoted = preg_quote($cap, '/');

        return preg_replace('/(?:'.$quoted.')+$/u', '', $value).$cap;
    }

    /**
     * 判断字符串是不是以给定字符串结尾
     * @param $haystack
     * @param $needles
     * @return bool
     */
    public static function endsWith($haystack, $needles)
    {
        foreach ((array) $needles as $needle) {
            if (substr($haystack, -strlen($needle)) === (string) $needle) {
                return true;
            }
        }

        return false;
    }

    /**
     * 判断字符串中是否包含给定的字符串
     * @param $haystack
     * @param $needles
     * @return bool
     */
    public static function contains($haystack, $needles)
    {
        foreach ((array) $needles as $needle) {
            if ($needle != '' && mb_strpos($haystack, $needle) !== false) {
                return true;
            }
        }

        return false;
    }

   /**
    * [模式匹配字符串]
    * @param  [type]  $pattern [description]
    * @param  [type]  $value   [description]
    * @return boolean          [description]
    */
    public static function is($pattern, $value)
    {
        if ($pattern == $value) {
            return true;
        }

        $pattern = preg_quote($pattern, '#');

        // Asterisks are translated into zero-or-more regular expression wildcards
        // to make it convenient to check if the strings starts with the given
        // pattern such as "library/*", making any string check convenient.
        $pattern = str_replace('\*', '.*', $pattern);

        return (bool) preg_match('#^'.$pattern.'\z#u', $value);
    }

    /**
     * 将字符串的每个单词首字母大写
     * @param $value
     * @return mixed
     */
    public static function studly($value)
    {
        $key = $value;

        if (isset(static::$studlyCache[$key])) {
            return static::$studlyCache[$key];
        }

        $value = ucwords(str_replace(['-', '_'], ' ', $value));

        return static::$studlyCache[$key] = str_replace(' ', '', $value);
    }

}