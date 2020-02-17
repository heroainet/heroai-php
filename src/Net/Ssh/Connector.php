<?php 
/**
 * @Copyright (C), 2013-, King.
 * @Name Connector.php
 * @Author King
 * @Version Beta 1.0 
 * @Date 2017年4月14日下午3:11:05
 * @Desc SSH链接
 * @Class List 
 * @Function List 
 * @History King 2017年4月14日下午3:11:05 0 第一次建立该文件
 *               King 2017年4月14日下午3:11:05 1 上午修改
 */
namespace Tiny\Net\Ssh;

/**
 * SSH连接器
 *
 * @package Tiny.Net.Ssh 
 * @since 2017年4月14日下午3:11:36
 * @final 2017年4月14日下午3:11:36
 */
class Connector {

    /**
     * 链接配置策略数组
     *
     * @var array
     */
    protected $_policy = array(
        'user' => 'root' ,
        'port' => '22' ,
        'passwd' => 'jinweimei2' ,
        'host' => '127.0.0.1'
    );
    
    protected $_connection = false;
    
    /**
     * 构造函数初始化SSH链接参数
     *
     * @param array $policy
     * @return void
     */
    public function __construct(array $policy = array())
    {
        if (empty($policy))
        {
            return;
        }
        $this->_policy = array_merge($this->_policy, $policy);
    }
    
    /**
     * 执行远程SSH命令
     *
     * @param string $execStr 执行命令
     * @return string
     */
    public function exec($execStr)
    {
        $conn = $this->_connect();
        if (! $conn)
        {
            $this->_disconnect();
            return false;
        }
        $stream = ssh2_exec($conn, $execStr);
        if (! $stream)
        {
            $this->_disconnect();
            return false;
        }
        
        $ret = null;
        try
        {
            stream_set_blocking($stream, true);
            stream_set_timeout($stream, 30);
            $ret = stream_get_contents($stream);
        }
        catch (\Exception $e)
        {
            $this->_disconnect();
        }
        return $ret;
    }
    
    /**
     * 回收链接
     *
     * @param void
     * @return void
     */
    public function __destruct()
    {
        $this->_disconnect();
    }
    
    /**
     * 链接
     *
     * @param void
     * @return $connection
     */
    protected function _connect()
    {
        if ($this->_connection)
        {
            return $this->_connection;
        }
    
        $conn = ssh2_connect($this->_policy['host'], $this->_policy['port']);
        if (! $conn)
        {
            return false;
        }
        if (! ssh2_auth_password($conn, $this->_policy['user'], $this->_policy['passwd']))
        {
            return false;
        }
        $this->_connection = $conn;
        return $conn;
    }
    
    /**
     * 断开链接
     *
     * @param void
     * @return void
     */
    protected function _disconnect()
    {
        if ($this->_connection)
        {
            $this->exec('echo "EXITING" && exit');
            ssh2_disconnect($this->_connection);
            $this->_connection = null;
        }
    }    
}
?>