<?php
/**
 * @Copyright (C), 2013-, King.
 * @Name ConsoleRequest.php
 * @Author King
 * @Version Beta 1.0 
 * @Date 2017年4月4日下午8:47:47
 * @Desc
 * @Class List 
 * @Function List 
 * @History King 2017年4月4日下午8:47:47 0 第一次建立该文件
 *               King 2017年4月4日下午8:47:47 1 上午修改
 */
namespace Tiny\Mvc\Request;

use Tiny\Mvc\Request\Param\Readonly;

/**
 * 控制器请求类
 * 
 * @package Tiny.Application.Request
 * @since 2017年4月4日下午8:48:32
 * @final 2017年4月4日下午8:48:32
 */
class ConsoleRequest extends Base
{

    /**
     * 参数
     * 
     * @var
     *
     */
    protected $_argument = [];
    
    protected $_argv = [];

    /**
     * 路由URI
     * 
     * @var
     *
     */
    protected $uri;

    /**
     * 获取路由字符串
     * 
     * @param void
     * @return string
     */
    public function getRouterString()
    {
        return $this->uri;
    }

    /**
     * 设置路由参数
     * 
     * @param array $param 参数
     * @return void
     */
    public function setRouterParam(array $param)
    {
        $this->param->merge($param);
    }

    /**
     * 魔术函数获取变量的值
     * 
     * @param
     *
     * @return
     *
     */
    protected function _magicGet($key)
    {
        switch (strtolower($key))
        {
            case 'param':
                return new Readonly($this->_argument, $this->_filter);
            case 'server':
                return new Readonly($this->_server, $this->_filter);
            case 'path':
                return $this->_server['PATH'];
            case 'user':
                return $this->_server['USER'];
            case 'pwd':
                return $this->_server['PWD'];
            case 'lang':
                return $this->_server['LANG'];
            case 'php':
                return $this->_server['_'];
            case 'script':
                return $this->_server['PHP_SELF'];
            case '_filter':
                return $this->_getFilter();
            default:
                return false;
        }
    }

    /**
     * 构造函数,初始化
     * 
     * @param void
     * @return void
     */
    protected function __construct()
    {
        $this->_server = $_SERVER;
        
        $argv = $this->_server['argv'];
        $argc = $this->_server['argc'];

        if ($argc <= 1)
        {
            return;
        }
        $data = array_slice($argv, 1);
        foreach ($data as $k => $d)
        {	
            if (!$this->_parseArgument($d))
            {
            	$this->_argv[] = $d;
            }
        }
        
        $this->_argument = array_merge($this->_argument, $this->_argv);
    }

    /**
     * 解析参数
     * 
     * @param array $data 数据
     * @return void
     */
    protected function _parseArgument($d)
    {
    	if (preg_match("/^--([a-zA-Z][a-zA-Z0-9_]*)(=([^=]+))?$/", $d, $out))
        {
            $this->_argument[$out[1]] = isset($out[3]) ? $out[3] : true;
            return true;
        }
        
        if (preg_match("/^-([a-zA-Z][a-zA-Z0-9_]*)(=([^=]+))?$/", $d, $out))
        {
            $this->_argument[$out[1]] = isset($out[3]) ? $out[3] : true;
            return true;
        }
        
        if (preg_match("/^\/([a-zA-Z]+)\/([a-zA-Z]+)(\/([^=]*))?$/", $d, $out))
        {
            $this->_argument['uri'] = $out[0];
            $this->uri = $out[0];
            return true;
        }
        
        if (preg_match('/^([a-zA-Z]+)\/([a-zA-Z]+)(=(\d+))?$/', $d, $out))
        {
            if (! isset($out[4]))
            {
                $out[4] = 1;
            }
            $this->_argument['daemons'][] = array('c' => $out[1] ,'a' => $out[2] ,'n' => $out[4]);
            return true;
        }
    }
}
?>