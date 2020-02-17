<?php
/**
 * @Copyright (C), 2013-, King.
 * @Name Base.php
 * @Author King
 * @Version Beta 1.0 
 * @Date 2017年3月10日下午10:55:57
 * @Desc
 * @Class List 
 * @Function List 
 * @History King 2017年3月10日下午10:55:57 0 第一次建立该文件
 *               King 2017年3月10日下午10:55:57 1 上午修改
 */
namespace Tiny\Mvc\Controller;

use Tiny\Mvc\ApplicationBase;
use Tiny\Tiny;
/**
 * 控制器积类
 * 
 * @package Tiny.Application.Controller
 * @since 2017年3月12日下午2:57:20
 * @final 2017年3月12日下午2:57:20
 */
abstract class Base
{

    /**
     * 当前应用程序实例
     * 
     * @var \Tiny\Mvc\WebApplication
     */
    public $application;

    /**
     * 当前应用程序的状态和配置数据
     * 
     * @var \Tiny\Config\Configuration
     */
    public $properties;

    /**
     * 当前WEB请求参数
     * 
     * @var \Tiny\Mvc\Request\WebRequest
     */
    public $request;

    /**
     * 当前WEB请求响应实例
     * 
     * @var \Tiny\Mvc\Response\WebResponse
     */
    public $response;
    
    /**
     * 设置当前应用实例
     * 
     * @param void
     * @return void
     */
    public function setApplication(ApplicationBase $app)
    {
        $this->application = $app;
        $this->request = $app->request;
        $this->response = $app->response;
        $this->properties = $app->properties;
    }

    /**
     * 关闭或开启调试模块
     * 
     * @param bool $bool 是否输出调试模块
     * @return void
     */
    public function setDebug($bool)
    {
        $this->application->isDebug = $bool;
    }

    /**
     * 写入日志
     * 
     * @param string $id
     * @return bool
     */
    public function log($id, $message, $priority = 1, $extra = array())
    {
        return $this->application->getLogger()->log($id, $message, $priority, $extra);
    }

    /**
     * 执行动作前触发
     * 
     * @param void
     * @return void
     */
    public function onBeginExecute()
    {
    }

    /**
     * 结束后触发该事件
     * 
     * @param void
     * @return void
     */
    public function onEndExecute()
    {
        
    }

    /**
     * 初始化视图实例后执行该函数
     */
    public function onInitedViewer()
    {
        
    }
    
    /**
     * 给试图设置预定义变量
     * 
     * @param string || array $key
     * @return bool
     */
    public function assign($key, $value)
    {
        return $this->view->assign($key, $value);
    }

    /**
     * 解析视图模板，注入到响应实例里
     * 
     * @param $viewPath string 视图相对路径
     * @return void
     */
    public function parse($viewPath)
    {
        $body = $this->view->fetch($viewPath);
        $this->response->appendBody($body);
    }

    /**
     * 解析视图模板，并返回解析后的字符串
     * 
     * @param $viewPath string 视图相对路径
     * @return void
     */
    public function fetch($viewPath)
    {
        return $this->view->fetch($viewPath);
    }

    /**
     * 加载Model
     * 
     * @param string $modelName 模型名称
     * @return Tiny\Mvc\Model\Base 
     */
    public function getModel($modelName)
    {
        return $this->application->getModel($modelName);
    }

    /**
     * 调用另外一个控制器的动作
     * 
     * @param $cName string 控制器名称
     * @param $aName string 动作名称
     * @return void
     */
    public function toDispathcher($cName, $aName)
    {
        return $this->application->dispatch($cName, $aName);
    }

    /**
     * 输出格式化的JSON串
     * @param array ...$params
     */
    public funCtion outFormatJSON(...$params)
    {
        return $this->response->outFormatJSON(...$params);
    }
    
    /**
     * 魔法函数，加载视图层
     * 
     * @param $key string 属性名
     * @return Tiny\Mvc\Viewer\Viewer 视图层对象
     *         Tiny\Config\Configuration 默认配置对象池
     *         Tiny\Cache\Cache 默认缓存对象池
     *          Cookie操作对象
     *         session session操作对象
     *         lang 语言对象
     *         Model 尾缀为Model的模型对象
     */
    public function __get($key)
    {
        $ins = $this->_magicGet($key);
        if ($ins)
        {
            $this->{$key} = $ins;
        }
        if ('view' == $key)
        {
            $this->onInitedViewer();
        }
        return $ins;
    }

    /**
     * 魔术方式获取属性
     * 
     * @param string $key
     * @return mixed
     */
    protected function _magicGet($key)
    {
        switch ($key)
        {
            case 'view':
                return $this->application->getViewer();
            case 'config':
                return $this->application->getConfig();
            case 'cache':
                return $this->application->getCache();
            case 'lang':
                return $this->application->getLang();         
            case ('Model' == substr($key, - 5) && strlen($key) > 6):
                return $this->application->getModel(substr($key, 0, - 5));
            default:
                return false;
        }
    }
}
?>