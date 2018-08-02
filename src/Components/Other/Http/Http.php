<?php

namespace Holy\Components\Other\Http;


/**
 * 发送请求专用
 * Class HuaFeiDuo
 * @package component\huafeiduo
 */
class Http
{
    public static function sendRequest($url, array $params = [], $method = 'GET', $timeout = 60)
    {
        $queryString = http_build_query($params);

        $ch = curl_init();
        if (strtoupper($method) == 'GET') {
            if(strpos($url, '?')){
                $url .= '&' . $queryString;
            } else {
                $url .= '?' . $queryString;
            }

            curl_setopt($ch, CURLOPT_URL, $url);

        } else if (strtoupper($method) == 'POST'){
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $queryString);
            curl_setopt($ch, CURLOPT_POST, TRUE);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

        $result = curl_exec($ch);

        curl_close($ch);

        return $result;
    }
}