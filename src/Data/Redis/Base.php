<?php
/**
 * @Copyright (C), 2013-, King.
 * @Name Base.php
 * @Author King
 * @Version 1.0
 * @Date: 2013-11-30上午04:28:01
 * @Description Redis的数据结构基类
 * @Class List
 * @Function
 * @History <author> <time> <version > <desc>
 king 2013-11-30上午04:28:01  1.0  第一次建立该文件
 */
namespace Tiny\Data\Redis;

use Tiny\Data\DataException;
/**
 * Redis的数据结构基类
 * 
 * @package Tiny.Data.Redis
 * @since 2013-11-30上午05:20:21
 * @final 2013-11-30上午05:20:21
 */
abstract class Base
{

    /**
     * 字符类型
     * 
     * @var string
     */
    const TYPE_STRING = \Redis::REDIS_STRING;

    /**
     * SET类型
     * 
     * @var string
     */
    const TYPE_SET = \Redis::REDIS_SET;

    /**
     * LIST类型
     * 
     * @var string
     * 
     */
    const TYPE_LIST = \Redis::REDIS_LIST;

    /**
     * ZSET类型
     * 
     * @var string
     */
    const TYPE_ZSET = \Redis::REDIS_ZSET;

    /**
     * HASH类型
     * 
     * @var string
     */
    const TYPE_HASH = \Redis::REDIS_HASH;

    /**
     * 未知类型
     * 
     * @var string
     */
    const TYPE_NOT_FOUND = \Redis::REDIS_NOT_FOUND;

    /**
     * redis操作实例
     * 
     * @var \Redis
     */
    protected $_redis = null;

    /**
     * 操作键名称
     * 
     * @var string
     */
    protected $_key = '';

    /**
     * 构造函数
     * 
     * @param \Redis $redis redis连接实例
     * @param string $key redis的操作键名称
     * @return void
     */
    public function __construct($redis, $key)
    {
        $this->_redis = $redis;
        $this->_key = (string) $key;
        if (is_null($this->_redis))
        {
            throw new DataException('初始化Redis结构失败:Redis实例无效');
        }
        if ('' == $key)
        {
            throw new DataException('初始化Redis结构失败:key无效');
        }
    }

    /**
     * 删除
     * 
     * @param void
     * @return bool
     */
    public function delete()
    {
        return $this->_redis->delete($this->_key);
    }

    /**
     * 是否存在该键
     * 
     * @param void
     * @return bool
     */
    public function exists()
    {
        return $this->_redis->exists($this->_key);
    }

    /**
     * 设置过期时间
     * 
     * @param int $timeout 过期秒数
     * @return bool
     */
    public function expire($timeout = 0)
    {
        return $this->_redis->expire($this->_key, $timeout);
    }

    /**
     * 设置一个过期的时间戳
     * 
     * @param int $timeStamp 时间戳
     * @return bool
     */
    public function expireAt($timeStamp)
    {
        return $this->_redis->expireAt($this->_key, $timeStamp);
    }

    /**
     * 返回存活时间
     * 
     * @param void
     * @return int
     */
    public function ttl()
    {
        return $this->_redis->ttl();
    }

    /**
     * 返回键值类型
     * 
     * @param void
     * @return string
     */
    public function type()
    {
        return $this->_redis->type($this->_key);
    }
}
?>