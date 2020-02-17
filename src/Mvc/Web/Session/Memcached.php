<?php
/**
 *
 * @Copyright (C), 2011-, King.
 * @Name  SessionMemcache.php
 * @Author  King
 * @Version  Beta 1.0
 * @Date: Sat Nov 12 23:16 52 CST 2011
 * @Description
 * @Class List
 *  	1.
 *  @History
 *      <author>    <time>                        <version >   <desc>
 *        King     2012-5-14上午08:22:34  Beta 1.0           第一次建立该文件
 *
 */
namespace Tiny\Mvc\Web\Session;

use Tiny\Data\Memcached\Schema;
use Tiny\Tiny;

/**
 * Session后端Redis适配器
 * 
 * @package Tiny.MVC.Http.Session
 * @since : 2013-4-13上午02:27:53
 * @final : 2013-4-13上午02:27:53
 */
class Memcached implements ISession
{

    /**
     * Redis的data操作实例
     * 
     * @var Schema
     * @access protected
     */
    protected $_schema;

    /**
     * 默认的服务器缓存策略
     * 
     * @var array
     * @access protected
     */
    protected $_policy = array('lifetime' => 3600);

    /**
     * 初始化构造函数
     * 
     * 
     * @param array $policy 配置
     * @return void
     */
    function __construct(array $policy = array())
    {
        $this->_policy = array_merge($this->_policy, $policy);
    }

    /**
     * 打开Session
     * 
     * 
     * @param void
     * @return void
     */
    public function open()
    {
        return true;
    }

    /**
     * 关闭Session
     * 
     * 
     * @param void
     * @return void
     */
    public function close()
    {
        return true;
    }

    /**
     * 读Session
     * 
     * 
     * @param string $sessionId Session身份标示
     * @return string
     */
    public function read($sessionId)
    {
        return $this->_getSchema()->get($sessionId);
    }

    /**
     * 写Session
     * 
     * 
     * @param string $sessionId SessionID标示
     * @param string $sessionValue Session值
     * @return bool
     */
    public function write($sessionId, $sessionValue)
    {
        return $this->_getSchema()->set($sessionId, $sessionValue, $this->_policy['lifetime']);
    }

    /**
     * 注销某个变量
     * 
     * 
     * @param string $sessionId Session身份标示
     * @return bool
     */
    public function destroy($sessionId)
    {
        return $this->_getSchema()->delete($sessionId);
    }

    /**
     * 自动回收过期变量
     * 
     * 
     * @param void
     * @return bool
     */
    public function gc()
    {
        return true;
    }

    /**
     * 获取redis操作实例
     * 
     * @access protected
     * @param void
     * @return Schema
     */
    protected function _getSchema()
    {
        if (! $this->_schema)
        {
            $data = Tiny::getApplication()->getData();
            $dataId = $this->_policy['dataid'];
            $schema = $data[$dataId];
            if (! $schema instanceof Schema)
            {
                throw new SessionException("dataid:{$dataId}不是Tiny\Data\Memcached\Schema的实例");
            }
            $this->_schema = $schema;
        }
        return $this->_schema;
    }
}
?>