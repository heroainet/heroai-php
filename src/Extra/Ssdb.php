<?php 
/**
*
* @Copyright (C), 2013 tinycn@qq.com
* @Name  Ssdb.php
* @Author  King
* @Version  1.0
* @Date:  2014-5-22下午5:00:01
* @Description 
* @Class List
*      1.
*  @Function List
*   1.
*  @History
*      <author>    <time>                     <version >                  <desc>
*        King        2014-5-22下午5:00:01          1.0                     第一次建立该文件
*
*/
namespace Tiny\Extra;

const SSDB_VERSION = '1.6.8';
include_once __DIR__ . DS . '__Extras' . DS . 'ssdb-' . SSDB_VERSION . DS . 'SSDB.php';

/**
 * 引入ssdb API接口
 * @package  Tiny.Extra
 * @since : 2014-5-22下午6:52:18
 * @final : 2014-5-22下午6:52:18
 */
class Ssdb extends \SimpleSSDB
{
	
}
?>