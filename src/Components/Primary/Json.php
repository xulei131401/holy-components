<?php

namespace component\http;

class Json
{
    public static function array2json($arr, $object = true)
    {
        if (empty($arr)) {
            return $object ? "{}" : "[]";
        }

        $arr = self::encodeArr($arr, true);
        $json = json_encode($arr);
        return self::encodeArr($json, false);
    }

    public static function json2array($json)
    {
        if (!$json) {
            return [];
        }

        $result = json_decode($json, JSON_UNESCAPED_UNICODE);
        if (!$result || $result === FALSE || !is_array($result)) {
            return [];
        }

        return $result;
    }


    public static function escapeArray($data)
    {
        $replace = array(
            "\r" => "",
            "\n" => "\\n",
            "\t" => " ",
            "\\" => "\\\\",
            '"' => '\"'
        );

        if (is_string($data)) {
            $data = strtr($data, $replace);
            return $data;
        } else if (is_array($data)) {
            $newData = array();
            foreach ($data as $key => $val) {
                $newData[$key] = self::escapeArray($val);
            }

            return $newData;
        }

        return $data;
    }

    private static function encodeArr($data, $encode = true)
    {
        $replace = array(
            "\r" => "\\r",
            "\n" => "\\n",
            "\t" => " ",
            "\\" => "\\\\",
            '"' => '\"'
        );

        if (is_string($data)) {
            return $encode ? urlencode(strtr($data, $replace)) : urldecode($data);
        } else if (is_array($data)) {
            $newData = [];
            foreach ($data as $key => $val) {
                $key = $encode ? urlencode(strtr($key, $replace)) : urldecode($key);
                $newData[$key] = self::encodeArr($val, $encode);
            }

            return $newData;
        } else {
            return $data;
        }
    }

}
