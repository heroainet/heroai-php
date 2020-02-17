<?php

/**
 * @Copyright (C), 2011-, King
 * @Name RouterRule.php
 * @Author King
 * @Version Beta 1.0
 * @Date 2013-4-1下午03:41:59
 * @Description 路由规则
 * @Class List
 * 1.
 * @Function List
 * 1.
 * @History
 * <author> <time> <version > <desc>
 * King 2013-4-1下午03:41:59 Beta 1.0 第一次建立该文件
 */
namespace Tiny\Mvc\Router;

/**
 * 路由规则实现
 * 
 * @package Router
 * @since : Thu Dec 15 17 42 00 CST 2011
 * @final : Thu Dec 15 17 42 00 CST 2011
 */
class StaticPath implements IRouter
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
        $this->_resloveParam($out[5]);
        $a = $out[3];
        $c = explode('-', $out[1]);
        foreach ($c as & $i)
        {
            $i = ucfirst($i);
        }
        $c = join('', $c);
        $this->_params['c'] = $c;
        if ($a)
        {
            $this->_params['a'] = $a;
        }
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
        $reg = $reg ?: array();
        $pattern = "/^\/([a-z\-]+)\/?(([a-z]+)\/?)?(([a-z0-9-]*)" . $ext . ")?$/i";
        $index = strpos($routerString, "?");
        if ($index)
        {
            $routerString = substr($routerString, 0, $index);
        }
        
        foreach ($reg as $data)
        {
            $routerString = preg_replace($data[0], $data[1], $routerString);
        }
        
        $routerString = strtolower($routerString);
        if (preg_match($pattern, $routerString, $out))
        {
            $urls = explode('/', $routerString);
            if (count($urls) == 3)
            {
                if (! preg_match("/^([a-z]+)$/", $urls[2]))
                {
                    $out[5] = strlen($ext) > 0 ? str_replace($ext, '', $urls[2]) : $urls[2];
                    $out[3] = '';
                }
            }
            return $out;
        }
        return false;
    }

    /**
     * 解析参数
     * 
     * @access protected
     * @param string $p 参数
     * @return void
     */
    protected function _resloveParam($p)
    {
        if (! $p || ($p[0] == '-') || ($p[strlen($p) - 1] == '-'))
        {
            return false;
        }
        $ps = explode('-', $p);
        for ($i = 0, $count = count($ps); $i < $count; $i = $i + 2)
        {
            if (! isset($ps[$i + 1]))
            {
                $this->_params[] = $ps[$i];
            }
            else
            {
                $this->_params[$ps[$i]] = $ps[$i + 1];
            }
        }
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