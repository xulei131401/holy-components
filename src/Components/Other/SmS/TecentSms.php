<?php

namespace Holy\Components\Other\SmS;
use component\Enumerate;
use component\http\Json;
use component\log\SpaLog;
use Qcloud\Sms\SmsMultiSender;
use Qcloud\Sms\SmsSingleSender;
use Qcloud\Sms\SmsVoicePromptSender;
use Qcloud\Sms\SmsVoiceVerifyCodeSender;
use think\Config;


/**
 * 腾讯短信SDK
 * Class TecentSms
 * @package component\sms
 */
class TecentSms
{
    const CH_NATION_CODE = '86';            //代表中国

    protected $_appId;
    protected $_appKey;

    protected $_singleSender;
    protected $_multiSender;
    protected $_voiceSender;
    protected $_voicePromptSender;

    protected $_data = [];

    private function __construct($appId = null, $appKey = null)
    {
        $config = Config::get('common.' . Enumerate::SMS)[Enumerate::SMS_TECENT];

        $this->_appId = $appId ?: $config[Enumerate::SMS_TECENT_APP_ID];
        $this->_appKey = $appKey ?: $config[Enumerate::SMS_TECENT_APP_KEY];
    }

    public static function getInstance($appId = null, $appKey = null)
    {
        return new self($appId, $appKey);
    }

    public function getSingleSender()
    {
        return $this->_singleSender;
    }

    public function getMultiSender()
    {
        return $this->_multiSender;
    }

    public function setSingleSender()
    {
        $this->_singleSender = new SmsSingleSender($this->_appId, $this->_appKey);
    }

    public function setMultiSender()
    {
        $this->_multiSender = new SmsMultiSender($this->_appId, $this->_appKey);
    }

    public function setVoiceSender()
    {
        $this->_voiceSender = new SmsVoiceVerifyCodeSender($this->_appId, $this->_appKey);
    }

    public function setVoicePromptSender()
    {
        $this->_voicePromptSender = new SmsVoicePromptSender($this->_appId, $this->_appKey);
    }

    /**
     * 普通单发
     *
     * 普通单发需明确指定内容，如果有多个签名，请在内容中以【】的方式添加到信息内容中，否则系统将使用默认签名。
     *
     * @param int    $type        短信类型，0 为普通短信，1 营销短信
     * @param string $nationCode  国家码，如 86 为中国
     * @param string $mobile      不带国家码的手机号,如果是数组代表群发，最多支持 200 个号码
     * @param string $msg         信息内容，必须与申请的模板格式一致，否则将返回错误
     * @param string $extend      扩展码，可填空串
     * @param string $ext         服务端原样返回的参数，可填空串
     * @return string             应答json字符串，详细内容参见腾讯云协议文档
     */
    public function sendNoTemplate($type, $mobile, $msg, $nationCode = self::CH_NATION_CODE, $extend = "", $ext = "")
    {
        $data = [$type, $nationCode, $mobile, $msg, $extend, $ext];
        $errLog = Json::array2json($data);
        $jsonResult = '';
        try {
            if (is_array($mobile)) {
                $this->setMultiSender();
                $jsonResult = $this->_multiSender->send(...$data);
            } else {
                $this->setSingleSender();
                $jsonResult = $this->_singleSender->send(...$data);
            }

            $result = Json::json2array($jsonResult);
            return $this->setNotifyResult($result, $errLog, $jsonResult);

        } catch(\Exception $e) {
            $this->handleException($errLog, $jsonResult);
            return FALSE;
        }

    }

    /**
     * 指定模板单发或者群发
     *
     * @param string $nationCode    国家码，如 86 为中国
     * @param string $mobile        不带国家码的手机号，如果是数组代表群发，最多支持 200 个号码
     * @param int    $templateId    模板 id
     * @param array  $params        模板参数列表，如模板 {1}...{2}...{3}，那么需要带三个参数
     * @param string $sign          签名，如果填空串，系统会使用默认签名
     * @param string $extend        扩展码，可填空串
     * @param string $ext           服务端原样返回的参数，可填空串
     * @return bool
     */
    public function sendWithTemplate($mobile, $templateId = 0, $params = [], $sign = "", $nationCode = self::CH_NATION_CODE, $extend = "", $ext = "")
    {
        $data = [$nationCode, $mobile, $templateId, $params, $sign, $extend, $ext];
        $errLog = Json::array2json($data);
        $jsonResult = '';
        try {
            if (is_array($mobile)) {
                $this->setMultiSender();
                $jsonResult = $this->_multiSender->sendWithParam(...$data);
            } else {
                $this->setSingleSender();
                $jsonResult = $this->_singleSender->sendWithParam(...$data);
            }

            $result = Json::json2array($jsonResult);
            return $this->setNotifyResult($result, $errLog, $jsonResult);

        } catch(\Exception $e) {
            $this->handleException($errLog, $jsonResult);
            return FALSE;
        }
    }

    /**
     * 发送语音验证码
     *
     * @param string $nationCode  国家码，如 86 为中国
     * @param string $mobile 不带国家码的手机号
     * @param string $msg         信息内容，必须与申请的模板格式一致，否则将返回错误
     * @param int    $playTimes   播放次数，可选，最多3次，默认2次
     * @param string $ext         用户的session内容，服务端原样返回，可选字段，不需要可填空串
     * @return string 应答json字符串，详细内容参见腾讯云协议文档
     */
    public function sendVoice($mobile, $msg = '', $nationCode = self::CH_NATION_CODE, $playTimes = 2, $ext = "")
    {
        $data = [$nationCode, $mobile, $msg, $playTimes, $ext];
        $errLog = Json::array2json($data);
        $jsonResult = '';
        try {
            $this->setVoiceSender();
            $jsonResult = $this->_voiceSender->send(...$data);
            $result = Json::json2array($jsonResult);
            return $this->setNotifyResult($result, $errLog, $jsonResult);

        } catch(\Exception $e) {
            $this->handleException($errLog, $jsonResult);
            return FALSE;
        }

    }

    /**
     * 发送自定义语音通知
     *
     * @param string $nationCode  国家码，如 86 为中国
     * @param string $mobile 不带国家码的手机号
     * @param int $promptType  语音类型，目前固定为2
     * @param string $msg         信息内容，必须与申请的模板格式一致，否则将返回错误
     * @param int $playTimes   播放次数，可选，最多3次，默认2次
     * @param string $ext         用户的session内容，服务端原样返回，可选字段，不需要可填空串
     * @return string 应答json字符串，详细内容参见腾讯云协议文档
     */
    public function sendCustomVoiceNotify($mobile, $msg = '', $promptType = 2, $nationCode = self::CH_NATION_CODE, $playTimes = 2, $ext = "")
    {
        $data = [$nationCode, $mobile, $promptType, $playTimes, $msg, $ext];
        $errLog = Json::array2json($data);
        $jsonResult = '';
        try {
            $this->setVoicePromptSender();
            $jsonResult = $this->_voicePromptSender->send(...$data);
            $result = Json::json2array($jsonResult);
            return $this->setNotifyResult($result, $errLog, $jsonResult);

        } catch(\Exception $e) {
            $this->handleException($errLog, $jsonResult);
            return FALSE;
        }

    }

    public function setNotifyResult($result, $errLog = '', $jsonResult = '')
    {
        if (!$result || $result['result'] != 0) {
            SpaLog::getLogger('tecent_sms_error')->info("发送语音短信失败,参数：：{$errLog}|||，返回信息：{$jsonResult}");
            return FALSE;
        }

        if ($result && $result['result'] == 0) {
            $this->_data = $result;
            return TRUE;
        }

        return FALSE;
    }

    public function handleException($errLog = '', $jsonResult = '')
    {
        SpaLog::getLogger('tecent_custom_sms_exception')->info("发送语音短信异常,参数：：{$errLog}|||，返回信息：{$jsonResult}");
    }

    /**
     * 获取第三方的相应结果
     * @param $key
     * @return array|mixed
     */
    public function response($key = null)
    {
        if (!$key) {
            return $this->_data;
        }

        if (isset($this->_data['data'][$key])) {
            return $this->_data['data'][$key];
        }

        return isset($this->_data[$key]) ? $this->_data[$key] : [];
    }

}