<?php
/**
 * @Copyright (C), 2013-, King.
 * @Name Counter.php
 * @Author King
 * @Version 1.0 
 * @Date: 2013-12-4上午09:43:50
 * @Description 
 * @Class List 
 * @Function 
 * @History <author> <time> <version > <desc> 
                   king 2013-12-4上午09:43:50  1.0  第一次建立该文件
 */
namespace Tiny\Data\Memcached;

/**
 * memcached实现的计数器 
 * @package Tiny.Data.Member
 * @since 2013-11-30下午01:51:35
 * @final 2013-11-30下午01:51:35
 */
class Counter
{

	/**
    * Memcached操作实例
    * @var Schema
    * 
    */
	private $_schema;

	/**
    * memcached的键
    * @var string
    * 
    */
	private $_key;

	/**
    * 构造函数
    * 
    * @param Redis redis实例
    * @param string $key 键名
    * @return void
    */
	public function __construct(Schema $schema, $key)
	{
		$this->_schema = $schema;
		$this->_key = $key;
	}

	/**
    * 获取key的值
    * 
    * @param void
    * @return int
    */
	public function get()
	{
		return $this->_schema->get($this->_key);
	}

	/**
    * 自增 
    * 
    * @param int $step 步进  默认为1
    * @return bool
    */
	public function incr($step = 1)
	{
		return $this->_schema->incr($this->_key, $step);
	}

	/**
    * 自减
    * 
    * @param int $step 步进  默认为1
    * @return bool
    */
	public function decr($step = 1)
	{
		return $this->_schema->decr($this->_key, $step);
	}

	/**
    *  重置计数器
    * 
    * @param void
    * @return bool
    */
	public function reset()
	{
		return $this->_schema->set($this->_key, 0);
	}

	/**
    * 字符串化
    * 
    * @param void
    * @return string
    */
	public function __toString()
	{
		return (string)$this->get($this->_key);
	}
}
?>