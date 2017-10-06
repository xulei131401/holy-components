<?php
namespace Holy\Components\Primary;

class DateTime
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

    /**
     * 计算每个月有几天
     * @param $month
     * @param $year
     * @return int|string
     */
    public static function getDaysInMonth($month, $year) {
        $days = '';
        if ($month == 1 || $month == 3 || $month == 5 || $month == 7 || $month == 8 || $month == 10 || $month == 12)
            $days = 31;
        else if ($month == 4 || $month == 6 || $month == 9 || $month == 11)
            $days = 30;
        else if ($month == 2) {
            if (static::isLeapYear($year)) {
                $days = 29;
            } else {
                $days = 28;
            }
        }
        return $days;
    }

    /**
     * 判断是否为润年
     * @param $year
     * @return bool
     */
    public static function isLeapYear($year) {
        if (strtotime($year) !== false) {
            if ((($year % 4) == 0) && (($year % 100) != 0) || (($year % 400) == 0)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 日期转换显示（超过当前时间则显示刚刚）
     * @param $date
     * @return string
     */
    public static function formatTime($date) {
        $timer = strtotime($date);
        $diff = $_SERVER['REQUEST_TIME'] - $timer;//得到请求间隔的时长
        $day = floor($diff / 86400);
        $free = $diff % 86400;
        if($day > 0) {
            if(15 < $day && $day <30){
                return "半个月前";
            }elseif(30 <= $day && $day <90){
                return "1个月前";
            }elseif(90 <= $day && $day <187){
                return "3个月前";
            }elseif(187 <= $day && $day <365){
                return "半年前";
            }elseif(365 <= $day){
                return "1年前";
            }else{
                return $day."天前";
            }
        }else{
            if($free>0){
                $hour = floor($free / 3600);
                $free = $free % 3600;
                if($hour>0){
                    return $hour."小时前";
                }else{
                    if($free>0){
                        $min = floor($free / 60);
                        $free = $free % 60;
                        if($min>0){
                            return $min."分钟前";
                        }else{
                            if($free>0){
                                return $free."秒前";
                            }else{
                                return '刚刚';
                            }
                        }
                    }else{
                        return '刚刚';
                    }
                }
            }else{
                return '刚刚';
            }
        }
    }

    /**
     * 原生PHP生成日期时间的连续数组（
     * $userStepReports = [
        [
        'date' => '2017-02-06',
        'total' => 652,
        ],
        [
        'date' => '2017-03-01',
        'total' => 773,
        ],
        [
        'date' => '2017-03-02',
        'total' => 459,
        ],];
        $userStepReports,
        '2017-02-25',
        '2017-03-05',
        'date',
        ['total' => 0]
     * ）
     * @param $input
     * @param $startDate
     * @param $endDate
     * @param $dateProperty
     * @param $default
     * @return array
     */
    public static function getDataInTimeSpan($input, $startDate, $endDate, $dateProperty, $default)
    {
        $start = new \DateTime($startDate);
        $end   = new \DateTime($endDate);

        if ($start->diff($end)->invert === 1) {
            throw new \LogicException('开始时间不能大于结束时间');
        }

        $keyedInput = [];

        foreach ($input as $value) {
            $keyedInput[$value[$dateProperty]] = $value;
        }

        $tmpEnd = clone $end;
        $endAt   = $tmpEnd->modify('+1 day')->format('Y-m-d');
        $current = clone $start;
        $output  = [];

        while (($currentDate = $current->format('Y-m-d')) !== $endAt) {
//            $output[] = $keyedInput[$currentDate] ?? array_merge($default, [
//                    $dateProperty => $currentDate,
//                ]);

             $output[] = isset($keyedInput[$currentDate]) ? $keyedInput[$currentDate] : array_merge([$dateProperty => $currentDate], $default);
            //改变时间戳,每次+1天
            $current->modify('+1 day');
        }

        return $output;
    }

}

/* End of file Datetime.php */