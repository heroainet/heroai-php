<?php
/**
 * @Copyright (C), 2013-, King.
 * @Name PathInfo.php
 * @Author King
 * @Version Beta 1.0 
 * @Date 2019年11月20日下午7:28:25
 * @Description 
 * @Class List 1.
 * @Function List 1.
 * @History King 2019年11月20日下午7:28:25 第一次建立该文件
 *                 King 2019年11月20日下午7:28:25 修改
 * 
 */
namespace Tiny\Mvc\Router;

/**
 * 路径路由
 *
 * @package class
 * @since 2019年11月26日下午2:07:07
 * @final 2019年11月26日下午2:07:07
 */
class PathInfo implements IRouter 
{
    
    /**
     * 解析存放URL参数的数组
     *
     * @var array
     */
    protected $_params = array();
    
    /**
     * 检查该规则是否成功
     *
     * @param $request \Tiny\Mvc\Request\Base
     * @return bool
     */
    public function checkRule(array $regRule, $routerString)
    {
        $ext = $regRule['ext'];
        if (! $out = $this->_checkUrl($ext, $routerString))
        {
            return false;
        }
        list($c, $this->_params['a']) = $out;
        if($c[0] == "/" || $c[0] == "\\")
        {
            $c = substr($c, 1);
        }
        $this->_params['c'] = $c;
        return true;
    }
    
    /**
     * 检测URL是否符合路由规则
     *
     * @access protected
     * @param string $url
     * @return
     *
     */
    protected function _checkUrl($ext, $routerString)
    {
        $pattern = "/^((\/[a-z]+)*)(\/([a-z]+))(\/|" . $ext . ")?$/i";
        $index = strpos($routerString, "?");
        if ($index)
        {
            $routerString = substr($routerString, 0, $index);
        }
        
        if (preg_match($pattern, $routerString, $out))
        {
            if (!$out[1 && !$ext])
            {
                $c = $out[4];
                return [$c, ''];
                
            }
            $c = $out[1];
            if ($ext && $out[5] == $ext)
            {
                $a = $out[4];
            }
            elseif ($out[5] == '/')
            {
                $c .= $out[3];
                $a = 'index';
            }
            else
            {
                $a = $out[4];
            }

            return [$c, $a];
        }
        return false;
    }
    
    /**
     * 获取路由解析后的URL参数
     *
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