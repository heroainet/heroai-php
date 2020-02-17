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
namespace Tiny\Data\Ssdb;

use Tiny\Data\ISchema;
use Tiny\Extra\Ssdb;

/**
 * ssdb的操作类
 * 
 * @package Tiny.Data.Ssdb
 * @since 2013-11-30上午02:36:20
 * @final 2013-11-30上午02:36:20
 */
class Schema implements ISchema
{

    /**
     * 连接实例
     * 
     * @var Ssdb
     */
    protected $_conn;

    /**
     * 默认的服务器缓存策略
     * 
     * @var array
     */
    protected $_policy = array(
        'host' => '127.0.0.1',
        'port' => 8888,
        'timeout' => 100
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
     * @return Ssdb
     */
    public function connect()
    {
        if ($this->_conn)
        {
            return $this->_conn;
        }
        if (! (int) $this->_policy['port'])
        {
            $this->_policy['port'] = 8888;
        }
        if (! $this->_policy['host'])
        {
            $this->_policy['host'] = '127.0.0.1';
        }
        $this->_conn = new Ssdb($this->_policy['host'], $this->_policy['port'], $this->_policy['timeout'] * 2000);
        return $this->_conn;
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
     * @return Ssdb
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
     * 调用连接实例的函数
     * 
     * @param string $method 函数名称
     * @param array $params 参数组
     * @return mixed type
     */
    public function __call($method, $params)
    {
        return call_user_func_array(array($this->connect(),$method
        ), $params);
    }
}
?>