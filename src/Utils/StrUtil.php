<?php

namespace App\Utils;

class StrUtil
{

    /**
     * 过滤掉非UTF-8特殊字符
     *
     * utf8 编码表
     * Unicode符号范围           | UTF-8编码方式
     * u0000 0000 - u0000 007F  | 0xxxxxxx
     * u0000 0080 - u0000 07FF  | 110xxxxx 10xxxxxx
     * u0000 0800 - u0000 FFFF  | 1110xxxx 10xxxxxx 10xxxxxx
     * @param string $str
     * @return string
     */
    public static function filterMalformedUtf8($str)
    {
        $re = '';
        $length = strlen($str);

        for ($i = 0; $i < $length; $i++) {
            $len = self::detectEncodingLength(ord($str[$i]));
            if ($i + $len > $length) {
                break;
            }
            switch ($len) {
                case 1:
                    $re .= $str[$i];
                    break;
                case 2:
                    if (self::detectEncodingLength(ord($str[$i + 1])) == 0) {
                        $re .= $str[$i] . $str[$i + 1];
                    }
                    $i = $i + 1;
                    break;
                case 3:
                    if (self::detectEncodingLength(ord($str[$i + 1])) == 0 && self::detectEncodingLength(ord($str[$i + 2])) == 0) {
                        $re .= $str[$i] . $str[$i + 1] . $str[$i + 2];
                    }
                    $i = $i + 2;
                    break;
                case 4:
                    if (self::detectEncodingLength(ord($str[$i + 1])) == 0 && self::detectEncodingLength(ord($str[$i + 2])) == 0 && self::detectEncodingLength(ord($str[$i + 3])) == 0) {
                        $re .= $str[$i] . $str[$i + 1] . $str[$i + 2] . $str[$i + 3];
                    }
                    $i = $i + 3;
                    break;
            }
        }
        return $re;
    }

    /**
     * 检测字符编码长度
     *
     * @param int $ascii
     * @return int  0表示
     */
    public static function detectEncodingLength($ascii)
    {
        if ($ascii < 128) {
            return 1;
        } else if ($ascii < 192) {
            return 0;
        } else if ($ascii < 224) {
            return 2;
        } else if ($ascii < 240) {
            return 3;
        } else if ($ascii < 248) {
            return 4;
        }
        return -1;
    }

}