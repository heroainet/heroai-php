<?php
/**
 * @Copyright (C), 2013-, King.
 * @Name Schema.php
 * @Author King
 * @Version 1.0
 * @Date: 2013-11-30上午02:35:09
 * @Description
 * @Class List
 * @Function
 * @History <author> <time> <version > <desc>
 king 2013-11-30上午02:35:09  1.0  第一次建立该文件
 */
namespace Tiny\Data\Redis;

use Tiny\Data\ISchema;

/**
 * redis的操作类
 * 
 * @package Tiny.Data.Redis
 * @since 2013-11-30上午02:36:20
 * @final 2013-11-30上午02:36:20
 */
class Schema implements ISchema
{

    /**
     * 连接实例
     * 
     * @var \Redis
     */
    protected $_conn;

    /**
     * 默认的服务器缓存策略
     * 
     * @var array
     * @access protected
     */
    protected $_policy = array(
        'host' => '127.0.0.1',
        'port' => 6379,
        'lifetime' => 3600,  /*缓存生命周期*/
        'persistent' => true, /*是否使用持久链接*/
        'options' => null,    /*设置选项*/
        'auth' => null,
        'servers' => null
    );

    /**
     * 统一的构造函数
     * 
     * @param array $policy 默认为空函数
     * @return void
     */
    public function __construct(array $policy = array())
    {
        $this->_policy = array_merge($this->_policy, $policy);
    }

    /**
     * 开始连接
     * 
     * @param void
     * @return bool
     */
    public function connect()
    {
        if ($this->_conn)
        {
            return $this->_conn;
        }
        
        $policy = & $this->_policy;
        $policy['port'] = (int)$policy['port'] ?: 6379;
        $policy['db'] = (int)$policy['db'];
        
        if (is_array($policy['servers']))
        {
            $hosts = array();
            foreach ($policy['servers'] as $serv)
            {
                $hosts[] = $serv['host'] . ':' . $serv['port'];
            }
            $conn = new \RedisArray($hosts, $policy['servers']['params']);
        }
        else
        {
            $conn = new \Redis();
            if ($policy['persistent'])
            {
                $conn->pconnect($policy['host'], $policy['port'], $policy['lifetime']);
            }
            else
            {
                $conn->connect($policy['host'], $policy['port'], $policy['lifetime']);
            }
            if ($policy['auth'])
            {
                $conn->auth($policy['auth']);
            }
            if ($policy['db'])
            {
                $conn->select($policy['db']);
            }
        } /* end of if (is_array($policy['servers'])) */
        
        $this->_conn = $conn;
        $options = & $policy['options'];
        if (! is_array($options))
        {
            $options = array();
        }
        if (defined('\Redis::SERIALIZER_IGBINARY'))
        {
            $options[\Redis::OPT_SERIALIZER] = \Redis::SERIALIZER_IGBINARY;
        }
        foreach ($options as $k => $v)
        {
            $conn->setOption($k, $v);
        }
        return $conn;
    }

    /**
     * 返回策略
     * 
     * @param void
     * @return array
     */
    public function getPolicy()
    {
        return $this->_policy;
    }

    /**
     * 返回连接后的类或者句柄
     * 
     * @param void
     * @return \Redis
     */
    public function getConnector()
    {
        return $this->connect();
    }

    /**
     * 关闭或者销毁实例和链接
     * 
     * @param void
     * @return void
     */
    public function close()
    {
        $this->connect()->close();
    }

    /**
     * 获取计数器实例
     * 
     * @param string $key 键
     * @return Counter
     */
    public function createCounter($key)
    {
        return new Counter($this->connect(), $key);
    }

    /**
     * 创建一个队列对象
     * 
     * @param void
     * @return Queue
     */
    public function createQueue($key)
    {
        return new Queue($this->connect(), $key);
    }

    /**
     * 创建一个哈希表对象
     * 
     * @param void
     * @return HashTable
     */
    public function createHashTable($key)
    {
        return new HashTable($this->connect(), $key);
    }

    /**
     * 创建一个集合对象
     * 
     * @param void
     * @return Set
     */
    public function createSet($key)
    {
        return new Set($this->connect(), $key);
    }

    /**
     * 创建一个有序集合对象
     * 
     * @param void
     * @return Set
     */
    public function createSortSet($key)
    {
        return new SortSet($this->connect(), $key);
    }

    /**
     * 调用连接实例的函数
     * 
     * @param string $method 函数名称
     * @param array $params 参数组
     * @return mixed type
     */
    public function __call($method, $params)
    {
        return call_user_func_array(array($this->connect(), $method), $params);
    }
}
?>