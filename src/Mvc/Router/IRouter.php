<?php 
/**
 * @Copyright (C), 2013-, King.
 * @Name IRouter.php
 * @Author King
 * @Version Beta 1.0 
 * @Date 2017年3月10日下午6:45:57
 * @Desc
 * @Class List 
 * @Function List 
 * @History King 2017年3月10日下午6:45:57 0 第一次建立该文件
 *               King 2017年3月10日下午6:45:57 1 上午修改
 */
namespace Tiny\Mvc\Router;

/**
 * 路由器接口
 *
 * @package 
 * @since 2017年3月12日下午5:57:08
 * @final 2017年3月12日下午5:57:08
 */
interface  IRouter
{
    /**
     * 检查规则是否符合当前path
     * @param void
     * @return bool
     */
    public function checkRule(array $regRule, $routerString);
    
    /**
     * 获取解析后的参数，如果该路由不正确，则不返回任何数据
     * @param void
     * @return array || null
     */
    public function getParams(); 
}
?>