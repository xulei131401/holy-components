<?php
namespace Holy\Support;

use Carbon\Carbon;
class DateTime extends Carbon
{

    /**
     * 获取上月月末日期时间
     * @param string $formatter
     * @return false|string
     */
    public static function getEndOfLastMonth($formatter = "Y-m-d H:i:s")
    {
        return date($formatter, (mktime(0, 0, 0, date('m'), 1, date('Y')) - 1));
    }

    /**
     * 获取上月月初日期时间
     * @param string $formatter
     * @return false|string
     */
    public static function getBeginOfLastMonth($formatter = "Y-m-d H:i:s")
    {
        return date($formatter, (mktime(0, 0, 0, date('m') - 1, 1, date('Y'))));
    }

    /**
     * 获取当月月末日期时间
     * @param string $formatter
     * @return false|string
     */
    public static function getEndOfCurrentMonth($formatter = "Y-m-d H:i:s")
    {
        return date($formatter, (mktime(23, 59, 59, date('m'), date('t'), date('Y'))));
    }

    /**
     * 获取当前周的周一的日期
     * @param string $formatter
     * @return false|string
     */
    public static function getBeginningOfCurrentWeek($formatter = "Y-m-d H:i:s")
    {
        $weekday = date('w', time());
        $weekday = $weekday ? --$weekday : 6;
        return date($formatter, strtotime("- {$weekday} day"));
    }

    /**
     * 获取两个日期的日期差
     * @param $startDate
     * @param $endDate
     * @return float
     */
    public static function getDiffDate($startDate, $endDate)
    {
        $startDateArr = explode("-", $startDate);
        $endDateArr = explode("-", $endDate);
        $start = mktime(0, 0, 0, $startDateArr[1], $startDateArr[2], $startDateArr[0]);
        $end = mktime(0, 0, 0, $endDateArr[1], $endDateArr[2], $endDateArr[0]);
        $days = round(($end - $start) / 3600 / 24);
        return $days;
    }

    /**
     * 获取某个日期对应月的月末
     * @param $date (日期时间字符串)
     * @return bool|string
     */
    public static function getLastDayOfMonth($date)
    {
        $firstDay = date('Y-m-01', strtotime($date));
        $lastDay = date('Y-m-d', strtotime("$firstDay +1 month -1 day"));
        return $lastDay;
    }

    /**
     * 将日期格式化输出成为x月x日
     * @param $time
     * @return string
     */
    public static function getShowMonthDay($time)
    {
        $month = date('m', strtotime($time));
        if (substr($month, 0, 1) == 0) {
            $month = substr($month, 1, 1);
        }
        $day = date('d', strtotime($time));
        if (substr($day, 0, 1) == 0) {
            $day = substr($day, 1, 1);
        }
        return "{$month}月{$day}日";
    }

    /**
     * 获取某个日期对应周的最后一天(周日)
     * @param $date (日期时间字符串)
     * @return bool|string
     */
    public static function getLastDayOfWeek($date)
    {
        $date = date('Y-m-d', strtotime($date));
        $lastDay = date("Y-m-d", strtotime("$date Sunday"));
        return $lastDay;
    }


}

/* End of file Datetime.php */