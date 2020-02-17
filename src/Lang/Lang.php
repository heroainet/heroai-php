<?php
/**
 * @Copyright (C), 2011-, King.
 * @Name Lang.php
 * @Author King
 * @Version Beta 1.0
 * @Date: Sat May 12 18:23:40 CST 2012
 * @Description
 * @Class List
 * 1. 语言类
 * @Function List
 * 1.
 * @History
 * <author> <time> <version > <desc>
 * King Sat May 12 18:23:40 CST 2012 Beta 1.0 第一次建立该文件
 */
namespace Tiny\Lang;

use Tiny\Config\Configuration;

/**
 * 语言类
 * @package Tiny.Lang
 * @since Sat May 12 18:35:34 CST 2012
 * @final Sat May 12 18:35:34 CST 2012
 *
 */
class Lang implements \ArrayAccess
{
	/**
     * 单例
     * @var Lang
     */
	protected static $_instance;

	/**
     * 获取单例
     * @param void
     * @return Lang
     */
	public static function getInstance()
	{
		if (! self::$_instance)
		{
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
     * 语言数据文件目录
     * @var string
     */
	protected $_langPath = '';

	/**
     * 语言种类
     * @var string
     */
	protected $_locale = '';

	/**
     * 语言数据存放数组
     * @var array
     */
	protected $_data = array ();

	/**
     * 设置语言数据文件夹路径
     * @param string $path 文件夹路径
     * @return bool
     */
	public function setLangPath($path)
	{
		$this->_langPath = $path;
		return $this;
	}

	/**
     * 设置语言种类
     * @param string $locale 语言名称
     * @return Lang
     *
     */
	public function setLocale($locale)
	{
		$this->_locale = $locale;
		return $this;
	}

	/**
     * 执行翻译
     * @param string $code 字符串代码
     * @return string
     */
	public function translate($code)
	{
		$data = $this->_getData();
		$string = $data[$this->_locale . '.' . $code];
		if (func_num_args() > 1)
		{
			$args = func_get_args();
			array_shift($args);
			$string = vsprintf($string, $args);
		}
		return $string;
	}

	/**
	* ArrayAccess 获取某个语言编码的值
	* @param string $code 语言码
	* @return null || string
	*/
	public function offsetGet($code)
	{
		return $this->translate($code);
	}

	/**
	* ArrayAccess 设置某个语言编码的值  不可用
	* @param string $code 语言码
	* @param string $val 翻译后的值
	* @return false
	*/
	public function offsetSet($code, $val)
	{
		return false;
	}

	/**
	* ArrayAccess 去掉某个语言码内容 不可用
	* @param string $code 语言码
	* @return  false
	*/
	public function offsetUnset($code)
	{
		return false;
	}

	/**
	* ArrayAcess 是否存在该语言包代码
	* @param string $code 语言码
	* @return bool true存在 false 不存在
	*/
	public function offsetExists($code)
	{
		return $this->offsetGet($code) ? true : false;
	}

	/**
     * 获取语言数据配置实例
     * @param string $code 语言代码
     * @return Configuration
     */
	protected function _getData()
	{
		if (! $this->_data)
		{
			$this->_data = new Configuration($this->_langPath);
		}
		return $this->_data;
	}
}
?>