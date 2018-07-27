<?php

namespace component\Other\PhoneBill;

use component\Enumerate;
use component\http\Http;
use component\http\Json;
use component\unicode\UnicodeUtil;
use component\log\SpaLog;
use think\Config;

/**
 * 话费多专用
 * Class HuaFeiDuo
 * @package component\huafeiduo
 */
class HuaFeiDuo
{
    protected $_secretKey;
    protected $_apiKey;
    protected $_gateWay;
    protected $_notifyUrl;
    protected $_response = false;
    protected $_data = [];

    private function __construct($secretKey = null, $apiKey = null)
    {
        $config = Config::get('common.' . Enumerate::HUAFEIDUO_KEY);

        $this->_secretKey   = $secretKey ?: $config[Enumerate::HUAFEIDUO_SECRET_KEY];
        $this->_apiKey      = $apiKey ?: $config[Enumerate::HUAFEIDUO_API_KEY];
        $this->_gateWay     = $config[Enumerate::HUAFEIDUO_GATE_WAY];
        $this->_notifyUrl   = $config[Enumerate::HUAFEIDUO_NOTIFY_URL];
    }

    public static function getInstance($secretKey = null, $apiKey = null)
    {
        return new self($secretKey, $apiKey);
    }

    /**
     * 获取签名
     * @param array $params
     * @return string
     */
    public function getSign(array $params = [])
    {
        if (!$params) {
            return md5($this->_secretKey);
        }

        $params['api_key'] = $this->_apiKey;
        ksort($params);

        $paramString = '';
        foreach($params as $k=>$v) {
            $paramString .= "{$k}{$v}";
        }

        return md5($paramString . $this->_secretKey);
    }

    /**
     * 查询账户余额
     * @return bool|mixed
     *
     * 返回值： balance: "100"
     */
    public function getBalance()
    {
        $mod = 'account.balance';
        $params = [];
        return $this->request($params, $mod)->checkValidate();
    }

    /**
     * 检查手机号是否能下单充值，并获取下单(进货)价格，以及手机号运营商、归属地等相关信息
     * @param $cardWorth
     * @param $mobile
     * @return bool|mixed
     *
     * 返回值： price = 49.6，下单(进货)价格；card_worth = 50，充值面额；phone_number = 13006681888，手机号；area = 广东深圳，手机号归属地；platform = 联通，所属运营商
     */
    public function getPhoneInfo($cardWorth, $mobile)
    {
        $mod = 'order.phone.check';

        $params = [
            'card_worth'    => $cardWorth,
            'phone_number'  => $mobile,
        ];

        return $this->request($params, $mod)->checkValidate();
    }

    /**
     * 提交充值订单
     * @param $cardWorth
     * @param $mobile
     * @param $spOrderId
     * @return bool
     *
     * 返回值：order_id: "xxxxxxxx"
     */
    public function preRecharge($cardWorth, $mobile, $spOrderId)
    {
        $mod = 'order.phone.submit';

        $params = [
            'card_worth'    => $cardWorth,
            'phone_number'  => $mobile,
            'sp_order_id'   => $spOrderId,
            'notify_url'    => $this->_notifyUrl,
        ];

        return $this->request($params, $mod)->checkValidate();
    }

    /**
     * 查询充值订单状态
     * @param $spOrderId
     * @return bool
     *  返回值：order_status:  init(订单初始化) | recharging(充值中) | success(充值成功) | failure(充值失败)
     */
    public function getRechargeStatus($spOrderId)
    {
        $mod = 'order.phone.status';

        $params = array(
            'sp_order_id'=> $spOrderId,
        );

        return $this->request($params, $mod)->checkValidate();
    }

    /**
     * 获取充值订单信息
     * $orderId 话费多平台订单号，它和sp_order_id至少传一个
     * $spOrderId 商户订单号，它和order_id至少传一个
     *
     * @return bool
     *
     * 返回值：order_id	话费多平台订单号
        sp_order_id	商户订单号
        status	订单状态，init(订单初始化) | recharging(充值中) | success(充值成功) | failure(充值失败)
        phone_number	充值手机号
        price	订单下单(进货)价格
        card_worth	订单充值面额
        area	手机号归属地
        platform	运营商
        create_time	订单创建时间，格式为时间戳
        last_status_change_time	订单最近一次状态改变的时间(例如从充值中变为充值成功)，格式为时间戳
     *
     */
    public function getOrderInfo($orderId = null, $spOrderId = null)
    {
        if (!$orderId && !$spOrderId) {
            return FALSE;
        }

        $mod = 'order.phone.get';

        $params = array(
            'order_id'      => $orderId,
            'sp_order_id'   => $spOrderId,
        );

        return $this->request($params, $mod)->checkValidate();
    }

    /**
     * 发送请求并设置结果
     * @param array $params
     * @param $mod
     * @param string $method
     * @return $this
     */
    public function request(array $params = [], $mod, $method = 'GET')
    {
        $params['api_key'] = $this->_apiKey;
        $sign = $this->getSign($params);
        $params['sign'] = $sign;
        $params['mod'] = $mod;

        $jsonResult = Http::sendRequest($this->_gateWay, $params, $method);
        $result = Json::json2array($jsonResult);

        if (!$result || $result['status'] == 'failure') {
            //打印第三方接口信息
            $jsonResult = UnicodeUtil::unicodeToCh($jsonResult);
            SpaLog::getLogger('huafeiduo_error')->info("话费多接口地址：{$mod}, 返回信息：{$jsonResult}");

            return $this;
        }

        if ($result && $result['status'] == 'success') {
            $this->_data = $result;
            $this->_response = true;
        }

        return $this;
    }

    /**
     * 获取请求成功还是失败
     * @return bool
     */
    public function checkValidate()
    {
        return $this->_response;
    }

    /**
     * 校验回调是否成功
     * @param $params
     * @return bool
     */
    public function verifyNotify(array $params = [])
    {
        $notifyParam = ['order_id', 'status', 'worth', 'price', 'phone', 'sp_order_id'];

        $secret = '';
        foreach ($notifyParam as $value) {
            if (isset($params[$value])) {
                $secret .= $params[$value];
            } else {
                $params[$value] = '';
                $secret .= '';
            }
        }

        if ($params['sign'] == md5($secret . $this->_secretKey)) {
            return TRUE;
        }

        return FALSE;
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