<?php
/**
 * @Copyright (C), 2011-, King.
 * @Name Mcrypt.php
 * @Author King
 * @Version Beta 1.0
 * @Date: 2012-2-2
 * @Description 加密解密类
 * @Class List
 * 1. Mcrypt
 * @Function List
 * 1.
 * @History
 * <author> <time> <version > <desc>
 * King 2012-2-2 Beta 1.0 第一次建立该文件
 */
namespace Tiny\String;

/**
 * 加密方式
 * 
 * @package Tiny.String
 * @since 2013-12-11上午04:20:03
 * @final 2013-12-11上午04:20:03
 */
class Mcrypt
{

    /**
     * 加密
     * 
     * @var int
     *
     */
    const MCRYPT_ENCODE = 0;

    /**
     * 解密
     * 
     * @var int
     */
    const MCRYPT_DECODE = 1;

    /**
     * 防暴力破解的加密KEY
     * 
     * @var string
     */
    public static $key = 'TINY';

    /**
     * 解密函数
     * 
     * @param string $string 需要解密的字符串
     * @param string $key 加密键
     * @param int $expiry 过期时间
     * @return string
     */
    public static function decode($string, $key = '', $expiry = 0)
    {
        return self::_authcode($string, self::MCRYPT_DECODE, $key, $expiry);
    }

    /**
     * 加密函数
     * 
     * @param string $string 需要加密的字符串
     * @param string $key 加密键
     * @param int $expiry 过期时间
     * @return string
     */
    public static function encode($string, $key = '', $expiry = 0)
    {
        return self::_authcode($string, self::MCRYPT_ENCODE, $key, $expiry);
    }

    /**
     * 加密解密函数
     * 
     * @param string $string 需要加密的字符串
     * @param string $key 加密键
     * @param int $expiry 过期时间
     * @return string
     *
     */
    protected static function _authcode($string, $operation = self::MCRYPT_DECODE, $key = '', $expiry = 0)
    {
        $ckeyLength = 4;
        $key = md5($key ? $key : self::$key);
        $keya = md5(substr($key, 0, 16));
        $keyb = md5(substr($key, 16, 16));
        $keyc = $ckeyLength ? ($operation == self::MCRYPT_DECODE ? substr($string, 0, $ckeyLength) : substr(md5(microtime()), - $ckeyLength)) : '';
        $cryptkey = $keya . md5($keya . $keyc);
        $keyLength = strlen($cryptkey);
        $string = ($operation == self::MCRYPT_DECODE) ? base64_decode(substr($string, $ckeyLength)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
        $stringLength = strlen($string);
        $result = '';
        $box = range(0, 255);
        $rndkey = array();
        for ($i = 0; $i <= 255; $i ++)
        {
            $rndkey[$i] = ord($cryptkey[$i % $keyLength]);
        }
        for ($j = $i = 0; $i < 256; $i ++)
        {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        for ($a = $j = $i = 0; $i < $stringLength; $i ++)
        {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }
        if ($operation == self::MCRYPT_DECODE)
        {
            if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16))
            {
                return substr($result, 26);
            }
        }
        else
        {
            return $keyc . str_replace('=', '', base64_encode($result));
        }
    }
}
?>