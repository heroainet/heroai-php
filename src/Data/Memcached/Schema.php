<?php
/**
 * @Copyright (C), 2013-, King.
 * @Name Schema.php
 * @Author King
 * @Version 1.0
 * @Date: 2013-12-1上午05:32:31
 * @Description
 * @Class List
 * @Function
 * @History <author> <time> <version > <desc>
 king 2013-12-1上午05:32:31  1.0  第一次建立该文件
 */
namespace Tiny\Data\Memcached;

use Tiny\Data\ISchema;
use Tiny\Data\DataException;

/**
 * memcached操作实例
 * 
 * @package Tiny.Data.Memcached
 * @since 2013-12-1上午05:33:08
 * @final 2013-12-1上午05:33:08
 */
class Schema implements ISchema
{

    /**
     * memcache连接句柄
     * 
     * @var mixed handle
     * @access protected
     */
    protected $_conn;

    /**
     * 是否为memcached扩展
     * 
     * @var bool
     * @access protected
     */
    protected $_isMemcached = falses;

    /**
     * 是否开启缓存压缩
     * 
     * @var bool
     * @access protected
     */
    protected $_isCommpressed = true;

    /**
     * 默认的服务器缓存策略
     * 
     * @var array
     * @access protected
     */
    protected $_policy = array('servers' => array('host' => '127.0.0.1' ,'port' => 11211),                      /*缓存服务器设置*/
        'compressed' => true,   /*是否压缩缓存数据*/
        'lifetime' => 0,       /*缓存生命周期*/
   		'poolname' => null ,'pconnect' => true);

    /**
     * 初始化构造函数
     * 
     * 
     * @param array $policy 代理数据
     * @return void
     */
    function __construct(array $policy = array())
    {
        $this->_policy = array_merge($this->_policy, $policy);
        $this->_isMemcached = extension_loaded('memcached') ? true : false;
        $this->_isCommpressed = $this->_policy['commpressed'];
    }

    /**
     * 获取连接句柄
     * 
     * 
     * @param void
     * @return mixed Memcached
     */
    public function connect()
    {
        if ($this->_conn)
        {
            return $this->_conn;
        }
        $servers = $this->_policy['servers'];
        if (! is_array($servers))
        {
            throw new DataException('Data.Memcached连接失败:policy.servers不是有效的数组');
        }
        $policy = & $this->_policy;
        $this->_conn = $this->_isMemcached ? new \Memcached($policy['poolname']) : new \Memcache();
        /* 持久化连接 */
        $policy['servers'] = $servers;
        if ($this->_isMemcached)
        {
            $this->_conn->addServers($servers);
            $this->_conn->setOption(\Memcached::OPT_COMPRESSION, $this->_isCommpressed);
        }
        else
        {
            foreach ($servers as $serv)
            {
                if ($serv['weight'] > 0)
                {
                    $this->_conn->addServer($serv['host'], $serv['port'], $policy['pconnect'], $serv['werght']);
                }
                else
                {
                    $this->_conn->addServer($serv['host'], $serv['port'], $policy['pconnect']);
                }
            }
        }
        return $this->_conn;
    }

    /**
     * 返回策略
     * 
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
     * 
     * @param void
     * @return \Memcache
     */
    public function getConnector()
    {
        return $this->connect();
    }

    /**
     * 关闭或者销毁实例和链接
     * 
     * @access void
     * @param void
     * @return void
     */
    public function close()
    {
        $this->connect()->close();
    }

    /**
     * 写入缓存
     * 
     * 
     * @param string $key
     * @param mixed $data
     * @param array $policy
     * @return boolean
     */
    public function set($key, $value = null, $life = null)
    {
        if (is_array($key))
        {
            $life = $value;
        }
        $life = is_null($life) ? $this->_policy['lifetime'] : $life;
        $conn = $this->connect();
        if ($this->_isMemcached)
        {
            if (is_array($key))
            {
                return $conn->setMulti($key, $life);
            }
            return $conn->set($key, $value, $life);
        }
        $compressed = $this->_policy['compressed'] ? \MEMCACHE_COMPRESSED : 0;
        if (is_array($key))
        {
            foreach ($key as $k => $v)
            {
                $conn->set($k, $v, $compressed, $life);
                echo $conn->get($k);
                echo $v;
            }
            return true;
        }
        return $conn->set($key, $value, $compressed, $life);
    }

    /**
     * 读取缓存，失败或缓存撒失效时返回 false
     * 
     * 
     * @param string $id
     * @return mixed
     */
    public function get($key)
    {
        $conn = $this->getConnector();
        if ($this->_isMemcached)
        {
            if (is_array($key))
            {
                return $conn->getMulti($key);
            }
            return $conn->get($key);
        }
        if (is_array($key))
        {
            $rets = array();
            foreach ($key as $k)
            {
                $rets[$k] = $conn->get($k);
            }
            return $rets;
        }
        return $conn->get($key);
    }

    /**
     * 删除指定的缓存
     * 
     * 
     * @param string $key
     * @return boolean
     */
    public function delete($key, $timeout = 0)
    {
        return $this->getConnector()->delete($key, $timeout);
    }

    /**
     * 刷新所有的缓存数据
     * 
     * @access ： public
     * @param void
     * @return boolean
     */
    public function flush()
    {
        return $this->getConnector()->flush();
    }

    /**
     * 自增
     * 
     * 
     * @param $key string 其他需要求差集的键
     * @return void
     */
    public function incr($key, $step = 1)
    {
        return $this->getConnector()->increment($key, $step);
    }

    /**
     * 自减
     * 
     * 
     * @param $key string 其他需要求差集的键
     * @return void
     */
    public function decr($key, $step = 1)
    {
        return $this->getConnector()->decrement($key, $step);
    }

    public function createCounter($key)
    {
        return new Counter($this, $key);
    }

    /**
     * 获取服务端版本号
     * 
     * 
     * @param void
     * @return string
     */
    public function version()
    {
        return $this->getConnector()->getVersion();
    }

    /**
     * 返回一个包含所有可用memcache服务器状态的数组
     * 
     * 
     * @param void
     * @return array
     */
    public function stats()
    {
        return $this->getConnector()->getStats();
    }

    /**
     * 调用连接实例的函数
     * 
     * 
     * @param string $method 函数名称
     * @param array $params 参数组
     * @return mixed type
     */
    public function __call($method, $params)
    {
        return call_user_func_array(array($this->connect() ,$method), $params);
    }
}
?>