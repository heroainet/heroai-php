<?php
/**
 *
 * @Copyright (C), 2011-, King
 * @Name  Convert.php
 * @Author  King
 * @Version  Beta 1.0
 * @Date  2013-4-1下午02:54:29
 * @Description
 * @Class List
 *      1.
 *  @Function List
 *   1.
 *  @History
 *      <author>    <time>                        <version >   <desc>
 *        King      2013-4-1下午02:54:29  Beta 1.0           第一次建立该文件
 *
 */
namespace Tiny\Runtime;

/**
 * 将一个基本数据类型转换为另一个基本数据类型。
 * 
 * @package Tiny
 * @since : 2012-7-24上午01:45:13
 * @final : 2012-7-24上午01:45:13
 */
final class Convert
{

    /**
     * 将指定的字符串（它将二进制数据编码为 Base64 数字）转换为字符串。
     * 
     * @param string $data 指定的字符串
     * @return string
     */
    public static function base64Encode($data)
    {
        return base64_encode($data);
    }

    /**
     * 将指定的字符串从Base64解码为 普通字符串
     * 
     * @param string $data 需要转换的字符串
     * @return string
     */
    public static function base64Decode($data)
    {
        return base64_decode($data);
    }

    /**
     * 将输入的变量转换为bool类型
     * 
     * @param $var mixed 输入的变量
     * @return bool
     */
    public static function toBoolean($var)
    {
        return (bool) $var;
    }

    /**
     * 将输入的变量转换为int 根据php规则，默认为long int 64位
     * 

     * @param $var mixed 输入的变量
     * @return int
     */
    public static function toInt($var)
    {
        return intval($var);
    }

    /**
     * 将输入的变量转换为浮点数
     * 
     * @param $var mixed 输入的变量
     * @return float
     */
    public static function toFloat($var)
    {
        return floatval($var);
    }
}
?>