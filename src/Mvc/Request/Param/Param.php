<?php
/**
 * @Copyright (C), 2013-, King.
 * @Name Param.php
 * @Author King
 * @Version Beta 1.0 
 * @Date 2017年3月12日下午10:35:18
 * @Desc
 * @Class List 
 * @Function List 
 * @History King 2017年3月12日下午10:35:18 0 第一次建立该文件
 *               King 2017年3月12日下午10:35:18 1 上午修改
 */
namespace Tiny\Mvc\Request\Param;

/**
 * 请求参数实例
 * 
 * @package Tiny.Application.Request.Param
 *
 * @since 2017年3月12日下午10:35:54
 * @final 2017年3月12日下午10:35:54
 */
class Param implements \ArrayAccess, \Iterator, \Countable
{

    /**
     * 存储数据的数组
     * 
     * @var array
     */
    protected $_data;
    
    /**
     * 过滤器
     * @var \Tiny\Filter\IFilter
     */
    protected $_filter;
    
    /**
     * 构造函数
     * 
     * @param array $data 数据
     * @return void
     */
    public function __construct(array $data, \Tiny\Filter\IFilter $filter = NULL)
    {
        $this->_data = $data;
        if($filter)
        {
            $this->_filter = $filter;
        }
    }
    
    /**
     * 获取键
     * 
     * @param string $offset
     * @return
     *
     */
    public function get($offset = NULL, $isFormat = TRUE)
    {
        $data = $offset === NULL ? $this->_data : $this->_data[$offset];
        return (bool)$isFormat ? $this->_formatData($data) : $data;
    }
    
    /**
     * ArrayAccess get
     * 
     * @param string $offset 键
     * @return mixed
     */
    public function offsetGet($offset)
    {
        $data = $this->_data[$offset];
        return $this->_formatData($data);
    }

    /**
     * ArrayAccess set
     * 
     * @param string $offset 键
     * @param mixed $value;
     * @return true
     */
    public function offsetSet($offset, $value)
    {
        $this->_data[$offset] = $value;
        return true;
    }

    /**
     * ArrayAccess exists
     * 
     * @param string $offset 键
     * @return mixed
     */
    public function offsetExists($offset)
    {
        return isset($this->_data[$offset]);
    }

    /**
     * ArrayAccess unset
     * 
     * @param string $offset 键
     * @return true
     */
    public function offsetUnset($offset)
    {
        unset($this->_data[$offset]);
        return true;
    }

    /**
     * Iterator rewind
     * 
     * @param void
     * @return
     *
     */
    public function rewind()
    {
        return reset($this->_data);
    }

    /**
     * Iterator current
     * 
     * @param void
     * @return
     *
     */
    public function current()
    {
        $data = current($this->_data);
        return $this->_formatData($data);
    }

    /**
     * Iterator next
     * 
     * @param void
     * @return
     *
     */
    public function next()
    {
        return next($this->_data);
    }

    /**
     * Iterator key
     * 
     * @param void
     * @return
     *
     */
    public function key()
    {
        return key($this->_data);
    }
    
    /**
     * Iterator valid
     * 
     * @param void
     * @return
     *
     */
    public function valid()
    {
        return key($this->_data) !== null;
    }
    
    /**
     * countable
     * 
     * @param void
     * @return int
     */
    public function count()
    {
        return count($this->_data);
    }
    
    /**
     * 合并数据
     *
     * @param array $data
     * @return void
     */
    public function merge(array $data)
    {
        $this->_data = array_merge($this->_data, $data);
    }
    
    /**
     * tostring
     * 
     * @param void
     * @return string
     */
    public function __toString()
    {
        return var_export($this->_data, true);
    }
    
    /**
     * 魔法调用过滤器
     * @param string $method
     * @param array $args
     * @return void|mixed
     */
    public function __call($method, $args)
    {
        if (!$this->_filter)
        {
            return $args;
        }
        if (!(strlen($method) > 6 && 'format' == substr($method, 0, 6)))
        {
            return $args;
        }
        if ($args)
        {
            $key = $args[0];
            $args[0] = $this->_data[$key];
        }
        else
        {
            $args[0] = $this->_data;
        }
        return call_user_func_array(array($this->_filter, $method), $args);
    }

    /**
     * 过滤数据
     * @param $offset
     */
    protected function _formatData($data)
    {
        return $this->_filter->formatWeb($data);
    }
}
?>