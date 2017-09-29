<?php
/**
 * Created by PhpStorm.
 * User: 许磊
 * Date: 2017/9/29
 * Time: 17:26
 */

namespace Holy\Components\Primary;


class Mobile
{
    /**
     * 判断是否为安卓手机
     * @return bool
     */
    public static function isAndroid()
    {
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
            if(strpos($agent,'android') !== false)
                return true;
        }
        return false;
    }

    /**
     * 判断是否为IOS或者IPAD
     * @return bool
     */
    public function isIOS()
    {
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
            if (strpos($agent, 'iphone') || strpos($agent, 'ipad')) {
                return true;
            }
        }
        return false;
    }

    /**
     * 判断是否为微信内置浏览器打开
     * @return bool
     */
    public function isWechet()
    {
        if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
            return true;
        }
        return false;
    }

    /**
     * 判断当前设备，1：安卓；2：IOS；3：微信；0：可能是PC
     * @return int
     */
    public static function isDevice()
    {
        if($_SERVER['HTTP_USER_AGENT']){
            $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
            if(strpos($agent, 'micromessenger') !== false)
                return 3;
            elseif(strpos($agent, 'iphone')||strpos($agent, 'ipad'))
                return 2;
            else
                return 1;
        }
        return 0;
    }

    /**
     * 判断是PC端还是wap端访问
     * @return bool
     */
    public static function isMobile()
    {
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
        {
            return true;
        }
        // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset ($_SERVER['HTTP_VIA']))
        {
            // 找不到为flase,否则为true
            return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
        }
        // 脑残法，判断手机发送的客户端标志,兼容性有待提高
        if (isset ($_SERVER['HTTP_USER_AGENT']))
        {
            $clientkeywords = array ('nokia',
                'sony',
                'ericsson',
                'mot',
                'samsung',
                'htc',
                'sgh',
                'lg',
                'sharp',
                'sie-',
                'philips',
                'panasonic',
                'alcatel',
                'lenovo',
                'iphone',
                'ipod',
                'blackberry',
                'meizu',
                'android',
                'netfront',
                'symbian',
                'ucweb',
                'windowsce',
                'palm',
                'operamini',
                'operamobi',
                'openwave',
                'nexusone',
                'cldc',
                'midp',
                'wap',
                'mobile'
            );
            // 从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT'])))
            {
                return true;
            }
        }
        // 协议法，因为有可能不准确，放到最后判断
        if (isset ($_SERVER['HTTP_ACCEPT']))
        {
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html'))))
            {
                return true;
            }
        }
        return false;
    }

}