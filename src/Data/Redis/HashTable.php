<?php
/**
 * @Copyright (C), 2013-, King.
 * @Name HashTable.php
 * @Author King
 * @Version 1.0
 * @Date: 2013-11-30上午04:27:26
 * @Description
 * @Class List
 * @Function
 * @History <author> <time> <version > <desc>
 king 2013-11-30上午04:27:26  1.0  第一次建立该文件
 */
namespace Tiny\Data\Redis;

/**
 *
 * @package Tiny.Data.Redis
 *
 * @since 2013-11-30下午04:00:39
 * @final 2013-11-30下午04:00:39
 */
class HashTable extends Base
{

    /**
     * 删除哈希表里的键
     * 
     * @param $key string 键名
     * @return bool
     */
    public function del($key)
    {
        return $this->_redis->hDel($this->_key, $key);
    }

    /**
     * 检测哈希表里的某个键是否存在
     * 
     * @param $key string 键
     * @return bool
     */
    public function hExists($key)
    {
        return $this->_redis->hExists($this->_key, $key);
    }

    /**
     * 根据指定键获取哈希表里的值
     * 
     * @param $key string 键名
     * @return bool
     */
    public function get($key)
    {
        return (is_array($key) ? $this->_redis->hMGet($this->_key, $key) : $this->_redis->hGet($this->_key, $key));
    }

    /**
     * 获取哈希表里的所有值
     * 
     * @param void
     * @return array
     */
    public function getAll()
    {
        return $this->_redis->hGetAll($this->_key);
    }

    /**
     * 自增
     * 
     * @param $key string 其他需要求差集的键
     * @return void
     */
    public function incr($key, $step = 1)
    {
    	$num = (int)$this->_redis->hGet($this->_key, $key);
    	$num += $step;
    	$this->_redis->hset($this->_key, $key, $num);
    	return $num;
    }

    /**
     * 设置键值
     * 
     * @param string $key 键名 为array时 为设置多值
     * @param $value mixed 值 默认为null
     * @return bool
     */
    public function set($key, $value = null)
    {
        return (is_array($key) ? $this->_redis->hMSet($this->_key, $key) : $this->_redis->hSet($this->_key, $key, $value));
    }

    /**
     * 求差集并保存在指定的键里
     * 
     * @param string $outKey 保存差集的键
     * @param string $key 求差集的键
     * @return bool
     */
    public function createrCounter($key)
    {
        return new HashTable\Counter($this, $key);
    }
}
?>