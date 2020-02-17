<?php
/**
 * @Copyright (C), 2013-, King.
 * @Name Set.php
 * @Author King
 * @Version 1.0 
 * @Date: 2013-11-30下午04:34:02
 * @Description 
 * @Class List 
 * @Function 
 * @History <author> <time> <version > <desc> 
 king 2013-11-30下午04:34:02  1.0  第一次建立该文件
 */
namespace Tiny\Data\Redis;


/**
 * redis的set结构操作类
 * 
 * @package Tiny.Data.Redis
 * @since 2013-11-30下午04:34:36
 * @final 2013-11-30下午04:34:36
 */
class Set extends Base
{

    /**
     * 添加值到集合中
     * 
     * @param $val  mixed 值
     * @return bool
     */
    public function add($val)
    {
        return $this->_redis->sAdd($this->_key, $val);
    }

    /**
     * 移除集合里的某个值
     * 
     * @param $val mixed 值
     * @return bool
     */
    public function remove($val)
    {
        return $this->_redis->sRem($this->_key, $val);
    }

    /**
     * 检测某个数值是否是集合的成员
     * 
     * @param $val mixed 数值
     * @return bool
     */
    public function contains($val)
    {
        return $this->_redis->sIsMember($this->_key, $val);
    }

    /**
     * 集合的元素数目
     * 
     * @param void
     * @return int
     */
    public function size()
    {
        return $this->_redis->sSize($this->_key);
    }

    /**
     * 求差集
     * 
     * @param $key mixed 其他需要求差集的键
     * @return void
     */
    public function diff($key)
    {
        $args = func_get_args();
        array_unshift($args, $this->_key);
        return $this->_redis->sDiff($args);
    }

    /**
     * 求差集并保存在指定的键里
     * 
     * @param string $outKey 保存差集的键
     * @param string $key 求差集的键
     * @return bool
     */
    public function diffStore($outKey, $key)
    {
        $args = func_get_args();
        array_unshift($args, $outKey);
        $args[1] = $this->_key;
        return $this->_redis->sDiffStore($args);
    }

    /**
     * 求交集
     * 
     * @param $key string 其他需要求交集的键
     * @return void
     */
    public function inter($key)
    {
        $args = func_get_args();
        array_unshift($args, $this->_key);
        return $this->_redis->sInter($args);
    }

    /**
     * 求交集并保存在指定的键里
     * 
     * @param string $outKey 保存交集的键
     * @param string $key 求交集的键
     * @return bool
     */
    public function interStore($outKey, $key)
    {
        $args = func_get_args();
        array_unshift($args, $outKey);
        $args[1] = $this->_key;
        return $this->_redis->sInterStore($args);
    }

    /**
     * 求并集
     * 
     * @param $key string 其他需要求并集的键
     * @return void
     */
    public function union($key)
    {
        $args = func_get_args();
        array_unshift($args, $this->_key);
        return $this->_redis->sUnion($args);
    }

    /**
     * 求并集并保存在指定的键里
     * 
     * @param string $outKey 保存并集的键
     * @param string $key 求并集的键
     * @return bool
     */
    public function unionStore($outKey, $key)
    {
        $args = func_get_args();
        array_unshift($args, $outKey);
        $args[1] = $this->_key;
        return $this->_redis->sUnionStore($args);
    }

    /**
     * 随机返回并删除名称为key的set中一个元素
     * 
     * @param void
     * @return mixed
     */
    public function pop()
    {
        return $this->_redis->sPop();
    }

    /**
     * 随机取回一个集合中的值
     * 
     * @param void
     * @return mixed
     */
    public function rand()
    {
        return $this->_redis->sRandMember();
    }

    /**
     * 获取集合的所有成员
     * 
     * @param void
     * @return array
     */
    public function getMembers()
    {
        return $this->_redis->sGetMembers();
    }
}
?>