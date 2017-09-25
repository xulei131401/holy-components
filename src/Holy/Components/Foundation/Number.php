<?php
namespace Holy\Components\Foundation;

class Number
{
    /**
     * 金额分转元
     * @param $money
     * @return string
     */
    public static function fen2yuan($money)
    {
        return number_format($money / 100, 2, '.', '');
    }

    /**
     * 金额元转分
     * @param $money
     * @return int
     */
    public static function yuan2fen($money)
    {
        return intval($money * 100);
    }

    /**
     * 随机生成字符串（最长32位）
     * @param int $length ：字符串位数
     * @return int
     */
    public static function random($length)
    {
        $length = intval($length);
        $min = pow(10, ($length - 1));
        $max = $min * 10 - 1;
        return mt_rand($min, $max);
    }

    /**
     * （待定）
     * @param int $length
     * @return bool|number|string
     */
    public static function uniqid($length = 8)
    {
        $id = abs(crc32(uniqid()));
        $id = (string)$id;
        $len = strlen($id);
        if ($len > $length) {
            $id = substr($id, $len - $length);
        } else {
            while ($len < $length) {
                $id .= mt_rand(0, 9);
                $len++;
            }
        }

        return $id;
    }
}

/* End of file Number.php */