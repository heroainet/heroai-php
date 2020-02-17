<?php
/**
 * @Copyright (C), 2013-, King.
 * @Name Queue.php
 * @Author King
 * @Version 1.0 
 * @Date: 2013-11-30下午04:02:04
 * @Description 
 * @Class List 
 * @Function 
 * @History <author> <time> <version > <desc> 
 king 2013-11-30下午04:02:04  1.0  第一次建立该文件
 */
namespace Tiny\Data\Redis;

/**
 * 队列
 * 
 * @package Tiny.Data.Redis
 * @since 2013-11-30下午04:02:36
 * @final 2013-11-30下午04:02:36
 */
class Queue extends Base
{

    /**
     * 最后的值
     * 
     * @var string
     */
    protected $_lastValue;

    /**
     * 取出一个值
     * 
     * @param void
     * @return array
     */
    public function pop($timeout = 0)
    {
        $this->_lastValue = ($timeout > 0) ? $this->_redis->brPop($this->_key, (int) $timeout) : $this->_redis->rpop($this->_key);
        return $this->_lastValue;
    }

    /**
     * 按序列取值
     * 
     * @param int $start 起始位置
     * @param int $limit 取值间隔
     * @return array
     */
    public function range($start = 0, $limit = -1)
    {
        $res = $this->_redis->lrange($this->_key, $start, $limit);
        if (! is_array($res))
        {
            return array();
        }
        return $res;
    }

    /**
     * 回滚 最后一个值
     * 
     * @param void
     * @return void
     */
    public function callback()
    {
        if (! is_null($this->_lastValue))
        {
            return $this->push($this->_lastValue);
        }
    }

    /**
     * 压入一个值
     * 
     * @param array $val 压入的值
     * @return bool
     */
    public function push($val)
    {
        return $this->_redis->lPush($this->_key, $val);
    }

    /**
     * 获取队列长度
     * 
     * @param void
     * @return int
     */
    public function length()
    {
        return $this->_redis->lLen($this->_key);
    }

    /**
     * 删除队列
     * 
     * @param void
     * @return bool
     */
    public function del()
    {
        return $this->_redis->delete($this->_key);
    }
}
?>