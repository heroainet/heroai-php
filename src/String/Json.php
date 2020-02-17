<?php
/**
 *
 * @Copyright (C), 2011-, King.$i
 * @Name  Json.php
 * @Author  King
 * @Version  Beta 1.0
 * @Date  Mon Jan 02 13:18:37 CST 2012
 * @Description JSON格式的编码与解码类
 * @Class List
 *  	1. Json
 *  @Function List
 *   1.
 *  @History
 *      <author>    <time>                        <version >   <desc>
 *        King      Mon Jan 02 13:18:37 CST 2012  Beta 1.0           第一次建立该文件
 *
 */
namespace Tiny\String;

/**
 * JSON编码类
 * 
 * @package Tiny.String
 * @since Mon Jan 02 14:43:27 CST 2012
 * @final Mon Jan 02 14:43:27 CST 2012
 */
class Json
{

    /**
     * JSON解析错误
     *
     * @var array
     */
    const JSON_ERRORS = array(
            JSON_ERROR_NONE => null ,
            JSON_ERROR_DEPTH => 'Maximum stack depth exceeded' ,
            JSON_ERROR_STATE_MISMATCH => 'Underflow or the modes mismatch' ,
            JSON_ERROR_CTRL_CHAR => 'Unexpected control character found' ,
            JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON' ,
            JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded'
        );
    /**
     * JSON编码 目前只接受UTF-8编码的数据
     * 
     * @param $obj 接受UTF-8编码的任何数据类型,资源类型除外.
     * @return string
     */
    public static function encode($obj)
    {
        return json_encode($obj);
    }

    /**
     * JSON解码
     * 
     * @param string $str 待解码的JSON字符串
     * @param bool $assoc 返回为array或者是Object 默认为true 返回数组.
     * @return array || object
     */
    public static function decode($str, $assoc = true)
    {
        return json_decode($str, $assoc);
    }

    /**
     * 适应5.3以上版本.
     * 返回最后一个JSON错误
     * 
     * @param void
     * @return string 返回json解析的最后一个错误
     */
    public static function getLastError()
    {
        $error = json_last_error();
        return array_key_exists($error, self::JSON_ERRORS) ? self::JSON_ERRORS[$error] : "Unknown error ({$error})";
    }
}
?>