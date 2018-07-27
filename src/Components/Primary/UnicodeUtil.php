<?php

namespace component\unicode;


/**
 * 这个类主要是为了将话费多返回的信息里边的unicode字符编码转换为汉字，并打印到日志里边
 * Class UnicodeUtil
 * @package component\unicode
 */
class UnicodeUtil
{
    /**
     * Unicode字符转汉字
     */
    public static function unicodeToCh1($uniCodeStr)
    {
        //①Deprecated: preg_replace(): The /e modifier is deprecated, use preg_replace_callback instead
        //②这个地方的边界符号换成是#也是可以的
        //③不只有UCS-2BE，还有UCS-2LE等等
        return $uniCodeStr ? preg_replace("/\\\u([0-9a-f]{4})/ie", "iconv('UCS-2BE', 'UTF-8', pack('H4', '\\1'))", $uniCodeStr) : '';
    }


    public static function unicodeToCh2($uniCodeStr)
    {
        $match = function ($matches) {
            return  iconv('UCS-2BE', 'UTF-8', pack('H4', $matches[1]));
        };

        //①php7.0以上版本使用这个
        //②这个地方的边界符号换成是#也是可以的
        return $uniCodeStr ? preg_replace_callback("/\\\u([0-9a-f]{4})/", $match, $uniCodeStr) : '';
    }

    public static function unicodeToCh($uniCodeStr)
    {
        $match = function ($matches) {
            return mb_convert_encoding(pack('H*', $matches[1]), 'UTF-8', 'UCS-2BE');
        };

        //①php7.0以上版本使用这个
        //②这个地方的边界符号换成是#也是可以的
        return $uniCodeStr ? preg_replace_callback("/\\\u([0-9a-f]{4})/", $match, $uniCodeStr) : '';
    }

}