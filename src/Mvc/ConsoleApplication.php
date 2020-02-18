<?php
/**
 * @Copyright (C), 2013-, King.
 * @Name ConsoleApplication.php
 * @Author King
 * @Version Beta 1.0
 * @Date 2017年4月5日下午11:30:52
 * @Desc
 * @Class List
 * @Function List
 * @History King 2017年4月5日下午11:30:52 0 第一次建立该文件
 *               King 2017年4月5日下午11:30:52 1 上午修改
 */
namespace Tiny\Mvc;


use Tiny\Mvc\Response\ConsoleResponse;
use Tiny\Mvc\Request\ConsoleRequest;
use Tiny\Mvc\Console\Daemon;
/**
 * 命令行应用实例
 *
 * @package Tiny.Application
 * @since 2017年4月5日下午11:31:23
 * @final 2017年4月5日下午11:31:23
 */
class ConsoleApplication extends ApplicationBase
{
    /**
     * Daemon
     *
     * @var Daemon
     */
    protected $_daemon;

    /**
     * 设置守护进程实例
     *
     * @param Daemon 守护进程实例
     * @return void
     */
    public function setDaemon(Daemon $daemon)
    {
        $this->_daemon = $daemon;
    }

    /**
     * 运行
     *
     * @param void
     * @return void
     */
    public function run()
    {
        /*检测进入守护进程模式*/
        if ($this->request->param['daemon'])
        {
            $this->_bootstrap();
            $this->_daemon();
            return;
        }
        return parent::run();
    }

    /**
     * 初始化请求实例
     * @param void
     * @return void
     */
    protected function _initRequest()
    {
        $this->request = ConsoleRequest::getInstance();
        parent::_initRequest();
    }

    /**
     * 初始化响应实例
     * @param void
     * @return void
     */
    protected  function _initResponse()
    {
        $this->response = ConsoleResponse::getInstance();
    }

    /**
     * 守护进程模式
     *
     * @param void
     * @return
     */
    protected function _daemon()
    {
        if (!$this->_daemon)
        {
            $this->_daemon = new Daemon($this);
        }
        $this->_daemon->run();
    }
}
?>