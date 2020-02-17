<?php
/**
 * @Copyright (C), 2013-, King.
 * @Name Debug.php
 * @Author King
 * @Version Beta 1.0 
 * @Date 2017年3月12日下午2:05:36
 * @Desc
 * @Class List 
 * @Function List 
 * @History King 2017年3月12日下午2:05:36 0 第一次建立该文件
 *               King 2017年3月12日下午2:05:36 1 上午修改
 */
namespace Tiny\Mvc\Plugin;

use Tiny\Data\Data;
use Tiny\Mvc\ApplicationBase;
use Tiny\Runtime\ExceptionHandler;

/**
 * DEBUG插件
 * 
 * @package Tiny.Application.Plugin
 * @since 2017年3月12日下午2:05:40
 * @final 2017年3月12日下午2:05:40
 */
class Debug implements Iplugin
{

    /**
     * 当前应用实例
     * 
     * @var \Tiny\Mvc\ApplicationBase
     */
    protected $_app;

    /**
     * 开始时间
     * 
     * @var float
     */
    protected $_startTime = 0;

    /**
     * 执行间隔
     * 
     * @var
     *
     */
    protected $_interval = 0;

    /**
     * debug的视图文件夹
     * 
     * @var void
     */
    protected $_viewFolder;

    /**
     * 初始化
     * 
     * @param $app  ApplicationBase 当前应用实例
     * @return void
     */
    public function __construct(ApplicationBase $app)
    {
        $this->_app = $app;
        $this->_startTime = microtime(true);
        $this->_viewFolder = __DIR__ . DIRECTORY_SEPARATOR . 'DebugView' . DIRECTORY_SEPARATOR;
    }

    /**
     * Debug动作执行
     * 
     * @param string $aName 动作名称
     * @return void
     */
    public function onAction($aName)
    {
        $path = $this->_viewFolder . strtolower($aName) . '.php';
        if (is_file($path))
        {
            $interval = microtime(true) - $this->_startTime;
            $memory = number_format(memory_get_peak_usage(true) / 1024 / 1024, 4);
            $viewer = $this->_app->getViewer();
            $viewPaths = $viewer->getParsePaths();
            $viewAssign = $viewer->getAssigns();
            $viewer->assign(array(
                'debug' => $this,
                'debugInterval' => $interval,
                'debugMemory' => $memory,
                'debugViewPaths' => $viewPaths,
                'debugViewAssign' => $viewAssign,
                'datamessage' => Data::getQuerys(),
                'debugExceptions' => ExceptionHandler::getInstance()->getExceptions()
            ));
            $body = $viewer->fetch($path, true);
            $this->_app->response->appendBody($body);
        }
    }

    /**
     * 本次请求初始化时发生的事件
     * 
     * @param void
     * @return void
     */
    public function onBeginRequest()
    {
    }

    /**
     * 本次请求初始化结束时发生的事件
     * 
     * @param void
     * @return void
     */
    public function onEndRequest()
    {
    }

    /**
     * 执行路由前发生的事件
     * 
     * @param void
     * @return void
     */
    public function onRouterStartup()
    {
    }

    /**
     * 执行路由后发生的事件
     * 
     * @param void
     * @return void
     */
    public function onRouterShutdown()
    {
    }

    /**
     * 执行分发前发生的动作
     * 
     * @param void
     * @return void
     */
    public function onPreDispatch()
    {
    }

    /**
     * 执行分发后发生的动作
     * 
     * @param void
     * @return void
     */
    public function onPostDispatch()
    {
        if ($this->_app->isDebug)
        {
            $this->onAction('debug');
        }
    }
}
?>