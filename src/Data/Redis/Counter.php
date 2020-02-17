<?php
/**
 * @Copyright (C), 2013-, King.
 * @Name Counter.php
 * @Author King
 * @Version 1.0 
 * @Date: 2013-11-30下午01:51:04
 * @Description Redis计数器
 * @Class List 
 * @Function 
 * @History <author> <time> <version > <desc> 
 king 2013-11-30下午01:51:04  1.0  第一次建立该文件
 */
namespace Tiny\Data\Redis;

/**
 * 计数器
 * 
 * @package Tiny.Data.Redis
 * @since 2013-11-30下午01:51:35
 * @final 2013-11-30下午01:51:35
 */
class Counter extends Base
{

    /**
     * 获取字符串的值
     * 
     * @param void
     * @return int
     */
    public function get()
    {
        return $this->_redis->get($this->_key);
    }

    /**
     * 自增
     * 
     * @param int $step 步进 默认为1
     * @return bool
     */
    public function incr($step = 1)
    {
        $step = (int) $step;
        if ($step == 1)
        {
            $this->_redis->incr($this->_key);
        }
        else
        {
            $this->_redis->incrBy($this->_key, $step);
        }
    }

    /**
     * 自减
     * 
     * @param int $step 步进 默认为1
     * @return bool
     */
    public function decr($step = 1)
    {
        $step = (int) $step;
        if ($step == 1)
        {
            $this->_redis->decr($this->_key);
        }
        else
        {
            $this->_redis->decrBy($this->_key, $step);
        }
    }

    /**
     * 重置计数器
     * 
     * @param void
     * @return bool
     */
    public function reset()
    {
        return $this->_redis->set($this->_key, 0);
    }

    /**
     * 字符串化
     * 
     * @param void
     * @return string
     */
    public function __toString()
    {
        return (string) $this->get($this->_key);
    }
}
?>