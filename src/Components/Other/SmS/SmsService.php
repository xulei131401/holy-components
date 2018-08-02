<?php

namespace app\common\service\sms;


use component\Enumerate;
use Holy\Components\Other\SmS\SmsConfig;
use Holy\Components\Other\SmS\TecentSms;

class SmsService
{
    /**
     * 发送普通短信
     * @param $template
     * @param $mobile
     * @param array $param
     * @param string $sign
     * @return bool
     */
    public static function sendSMS($mobile, $template, array $param = [], $sign = '')
    {
        $sender = self::getSmsAgent();
        return $sender->sendWithTemplate($mobile, $template, $param, $sign);
    }

    public static function getSmsAgent()
    {
        //TODO::根据实际情况替换为框架配置
        $type = [];
        if ($type == Enumerate::SMS_TECENT) {
            return TecentSms::getInstance();
        }
    }

    /**
     * 话费多充值发短信
     * @param $mobile
     * @param array $param
     * @param string $sign
     * @return bool
     */
    public static function smsToPhoneBill($mobile, array $param = [], $sign = '')
    {
        return self::sendSMS($mobile, SmsConfig::TEMPLATE_ID_PHONE_BILL, $param, $sign);
    }

    /**
     * 兑换管理发短信
     * @param $mobile
     * @param array $param
     * @param string $sign
     * @return bool
     */
    public static function smsToExchange($mobile, array $param = [], $sign = '')
    {
        return self::sendSMS($mobile, SmsConfig::TEMPLATE_ID_EXCHANGE, $param, $sign);
    }
}