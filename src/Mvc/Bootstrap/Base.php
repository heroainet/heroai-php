<?php
/**
 * @Copyright (C), 2013-, King.
 * @Name Base.php
 * @Author King
 * @Version Beta 1.0 
 * @Date 2017年3月12日下午5:31:11
 * @Desc
 * @Class List 
 * @Function List 
 * @History King 2017年3月12日下午5:31:11 0 第一次建立该文件
 *               King 2017年3月12日下午5:31:11 1 上午修改
 */
namespace Tiny\Mvc\Bootstrap;

use Tiny\Mvc\ApplicationBase;

/**
 * 引导基类
 * 
 * @package Tiny.Application.Bootstrap
 * @since 2017年3月12日下午5:34:17
 * @final 2017年3月12日下午5:34:17
 */
abstract class Base
{

    /**
     * 当前App运行实例
     * 
     * @var ApplicationBase
     */
    protected $_app;

    /**
     * 执行引导程序初始化函数
     * 
     * @param void
     * @return void
     */
    final public function bootstrap(ApplicationBase $app)
    {
        $this->_app = $app;
        $methods = $this->_getBootstrapMethods();
        
        foreach ($methods as $method)
        {
            call_user_func_array(array($this ,$method), array('app' => $app));
        }
    }

    /**
     * 获取可供初始化执行的函数数组
     * 
     * @param void
     * @return array
     */
    final private function _getBootstrapMethods()
    {
        static $methods;
        if (is_array($methods))
        {
            return $methods;
        }
        $methods = array();
        $ms = get_class_methods($this);
        
        foreach ($ms as $method)
        {
            if (stripos($method, 'init') === 0)
            {
                
                $methods[] = $method;
            }
        }
        return $methods;
    }
}
?>