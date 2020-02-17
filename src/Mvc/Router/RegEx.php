<?php
/**
 *
 * @Copyright (C), 2011-, King
 * @Name  RouterRule.php
 * @Author  King
 * @Version  Beta 1.0
 * @Date  2013-4-1下午03:41:59
 * @Description 路由规则
 * @Class List
 *      1.
 *  @Function List
 *   1.
 *  @History
 *      <author>    <time>                        <version >   <desc>
 *        King      2013-4-1下午03:41:59  Beta 1.0           第一次建立该文件
 *
 */
namespace Tiny\Mvc\Router;


/**
 * 路由规则实现
 * 
 * @package Router
 * @since : Thu Dec 15 17 42 00 CST 2011
 * @final : Thu Dec 15 17 42 00 CST 2011
 */
class RegEx implements IRouter
{

    /**
     * 解析存放URL参数的数组
     * 
     * @var array
     */
    private $_params = array();

    /**
     * 检查该规则是否成功
     * 
     * @param $request \Tiny\Mvc\Request\Base
     * @return bool
     */
    public function checkRule(array $regRule, $routerString)
    {
        $ext = $regRule['ext'];
        $reg = $regRule['reg'];
        $regArray = $regRule['keys'];
        
        if (! preg_match($reg, $routerString, $out))
        {
            return false;
        }
        
        foreach ($regArray as $key => $value)
        {
            $v = $out[$value];
            if (strpos($v, '/') > - 1)
            {
                $v = explode('/', $v);
                foreach ($v as & $vi)
                {
                    $vi = ucfirst($vi);
                }
                $v = join('', $v);
            }
            $this->_params[$key] = $v;
        }
        return true;
    }

    /**
     * 获取路由解析后的URL参数
     * 
     * @param void
     * @return array
     */
    public function getParams()
    {
        return $this->_params;
    }
}
?>