<?php
namespace Holy\Components\Primary;

class Number
{
    protected static $randomFactor = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    protected static $randomNumber = '0123456789';

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

}

/* End of file Number.php */