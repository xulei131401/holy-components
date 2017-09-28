<?php
namespace Holy\Components\Primary;

class Regex
{
   private static $_regex_rules = array(
        'email' => '/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/',   //邮箱
        'mobile' => '/^(\+86 )?1[3-9][0-9]{9}$/',                       //手机
        'date' => '/^\d{4}\-\d{2}\-\d{2}$/'                             //日期
    );

   /**
    * [使用正则验证数据,成功返回匹配的数据，否则返回false]
    * @param  [type]  $value [description]
    * @param  [type]  $rule  [description]
    * @return boolean        [description]
    */
    public static function isValidate($value, $rule)
    {
        if (isset(self::$_regex_rules[strtolower($rule)])) {
            $rule = self::$_regex_rules[strtolower($rule)];
        }
        return preg_match($rule, $value, $matchs);  //返回匹配的次数,0次或者1次
    }

    /**
     * [验证 isGpsLocation]
     * @param  [type]  $location [纬度经度完整字符串]
     * @param  integer &$lat     [纬度]
     * @param  integer &$lng     [经度]
     * @return boolean           [description]
     */
    public static function isGpsLocation($location, &$lat = 0, &$lng = 0)
    {
        if (preg_match('/^([1-9]\d+\.\d+),([1-9]\d+\.\d+)$/', $location, $match)){
            $lat = $match[1];
            $lng = $match[2];
            return true;
        }
        return false;
    }

}

/* End of file Regex.php */
