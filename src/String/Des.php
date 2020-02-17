<?php
/**
 * @Copyright (C), 2011-, King.
 * @Name: Des.php
 * @Author: King
 * @Version: Beta 1.0
 * @Date: 2013-3-31上午10:52:07
 * @Description:
 * @Class List:
 * 1.
 * @Function List:
 * 1.
 * @History:
 * <author> <time> <version > <desc>
 * King 2013-3-31上午10:52:07 Beta 1.0 第一次建立该文件
 */
namespace Tiny\String;

/**
 * des加密 解密
 * 
 * @package Tiny.String
 * @since 2013-3-31下午01:02:07
 * @final 2013-3-31下午01:02:07
 */
class Des
{

    /**
     * 加密
     * 
     * @param string $input 输入的字符串
     * @param string $key 加密的密钥
     * @return string
     */
    public static function encrypt($input, $key)
    {
        $size = mcrypt_get_block_size('des', 'ecb');
        $input = self::_pkcs5Pad($input, $size);
        $td = mcrypt_module_open('des', '', 'ecb', '');
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        mcrypt_generic_init($td, $key, $iv);
        $data = mcrypt_generic($td, $input);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        $data = base64_encode($data);
        return $data;
    }

    /**
     * 解密
     * 
     * @param string $encrypted 已经加密的字符串
     * @param string $key 密钥
     * @return string
     */
    public static function decrypt($encrypted, $key)
    {
        $encrypted = base64_decode($encrypted);
        $td = mcrypt_module_open('des', '', 'ecb', '');
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        $ks = mcrypt_enc_get_key_size($td);
        mcrypt_generic_init($td, $key, $iv);
        $decrypted = mdecrypt_generic($td, $encrypted);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        $y = self::_pkcs5Unpad($decrypted);
        return $y;
    }

    /**
     * 字符加密
     * 
     * @param string $param 待解密的字符串
     * @param string $key 加密的密钥
     * @return string
     */
    public static function uEncode($str, $key)
    {
        $ret = '';
        $str = base64_encode($str);
        for ($i = 0; $i <= strlen($str) - 1; $i ++)
        {
            $dStr = substr($str, $i, 1);
            $int = ord($dStr);
            $int = $int ^ $key;
            $hex = strtoupper(dechex($int));
            $ret .= $hex;
        }
        return $ret;
    }

    /**
     * 字符解密
     * 
     * @param string $param 待解密的字符串
     * @param string $key 解密的密钥
     * @return string
     */
    public static function uDecode($str, $key)
    {
        $ret = '';
        for ($i = 0; $i <= strlen($str) - 1; 0)
        {
            $hex = substr($str, $i, 2);
            $dec = hexdec($hex);
            $dec = $dec ^ $key;
            $ret .= chr($dec);
            $i = $i + 2;
        }
        return base64_decode($ret);
    }

    /**
     * 打包
     * 
     * @param string $text 字符串
     * @param int $blocksize 长度
     * @return string
     */
    protected static function _pkcs5Pad($text, $blocksize)
    {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

    /**
     * 解包
     * 
     * @param string $text 需要解密的字符串
     * @return string
     */
    protected static function _pkcs5Unpad($text)
    {
        $pad = ord($text{strlen($text) - 1});
        if ($pad > strlen($text))
        {
            return false;
        }
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad)
        {
            return false;
        }
        return substr($text, 0, - 1 * $pad);
    }
}
?>