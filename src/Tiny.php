<?php
/**
 * @Copyright (C), 2013-, King.
 * @Name Tiny.php
 * @Author King
 * @Version Beta 1.0 @Date: 2013-11-11上午04:43:47
 * @Description 框架主体入口
 * @Class List 1.Registy 注册仓库 2.
 * @Function List 1.
 * @History King 2013-11-11上午04:43:47 0 第一次建立该文件
 *          King 2017-03-06上午修改
 * 
 */
namespace Tiny;

/*加载框架的运行时对象*/
require_once  __DIR__ . '/Runtime/Runtime.php';

use Tiny\Runtime\Runtime;

/**
 * 
 *
 * @package Tiny
 * @since 2019年11月12日上午10:11:04
 * @final 2019年11月12日上午10:11:04
 */
class Tiny
{
    /**
     * 应用程序加载实例
     *
     * @var \Tiny\MVC\ApplicationBase
     */
    protected static $_app;
    
    /**
     * 设置当前的Application实例
     *
     * @param 
     * @return
     */
    public static function setApplication(\Tiny\MVC\ApplicationBase $app)
    {
        self::$_app = $app;
    }
    
    /**
     * 获取当前的application实例
     *
     * @param void
     * @return \Tiny\MVC\ApplicationBase
     */
    public static function getApplication()
    {
        return self::$_app;
    }
    
    /**
     * 自动根据运行环境创建APP
     * @param string $appPath application工作目录
     * @param string $profile 配置文件路径
     * @param array $env runtime环境参数 仅第一次调用时设置有效
     * @return \Tiny\Mvc\ApplicationBase
     */
    public static function createApplication($appPath, $profile = NULL)
    {
        if (!self::$_app)
        {
            self::$_app =  Runtime::getInstance()->createApplication($appPath, $profile);
        }
        return self::$_app;
    }
    
    /**
     * 注册或者替换已有的Application
     * @param int $mode
     * @param string $className
     * @return void
     */
    public function regApplicationMap($mode, $className)
    {
        return Runtime::regApplicationMap($mode, $className);
    }
    
    /**
     * 设置Runtime的默认环境参数 仅在RunTime实例化前有效
     * @param array $env
     */
    public static function setENV(array $env)
    {
        return Runtime::setENV($env);
    }
}

?>