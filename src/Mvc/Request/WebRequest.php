<?php 
/**
 * @Copyright (C), 2013-, King.
 * @Name HttpRequest.php
 * @Author King
 * @Version Beta 1.0 
 * @Date 2017年3月8日下午4:33:02
 * @Desc
 * @Class List 
 * @Function List 
 * @History King 2017年3月8日下午4:33:02 0 第一次建立该文件
 *               King 2017年3月8日下午4:33:02 1 上午修改
 */
namespace Tiny\Mvc\Request;

use Tiny\Mvc\Request\Param\Readonly;
use Tiny\Mvc\ApplicationBase;

/**
 * Web请求
 * @package 
 * @since 
 * @final 
 */
class WebRequest extends Base
{   
    /**
     * 请求参数
     *
     * @var array
     */
    protected $_data;
    
    /**
     * 服务器参数
     *
     * @var array();
     */
    protected $_server;
    
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
        $this->_data['get'] = array_merge($this->_data['get'], $param);
        $this->_data['request'] = array_merge($this->_data['request'], $param);
    }
    
    /**
     * 魔术函数获取变量的值
     *
     * @param 
     * @return
     */    
    protected function _magicGet($key)
    {
        switch (strtolower($key))
        {
            case 'get' :
                return new Readonly($this->_data['get'], $this->_filter);
            case 'post' :
                return new Readonly($this->_data['post'], $this->_filter);
            case 'param' :
                return new Readonly($this->_data['request'], $this->_filter);
            case 'server' :
                return new Readonly($this->_server, $this->_filter);
            case 'cookie' :
                return $this->_app->getCookie($this->_data['cookie']);
            case 'session':
                return $this->_app->getSsession();
            case 'file' :
                return $this->_app->getFile();
            case 'files':
                return $this->_data['files'];
            case 'ip' :
                return $this->_getIp($this->_server);
            case 'url':
                return $this->_getUrl($this->_server);
            case 'uri':
                return $this->_server['REQUEST_URI'];
            case 'ishttps' :
                return (443 == $this->_server['SERVER_PORT']);
            case 'port' :
                return $this->_server['SERVER_PORT'];
            case 'pathinfo' :
                return $this->_server['PATH_INFO'];
            case 'ispost' :
                return 'POST' == $this->_server['REQUEST_METHOD'];
            case 'useragent' : 
                return $this->_server['HTTP_USER_AGENT'];
            case 'root' :
                return $this->_server['DOCUMENT_ROOT'];
            case 'referer':
                return $this->_server['HTTP_REFERER'];
            case 'host' :
                return $this->_server['HTTP_HOST'];
            case '_filter':
                return $this->_getFilter();
             default:
                return false; 
        }
    }

    /**
     * 设置当前应用实例
     *
     * @param ApplicationBase $app
     * @return void
     */
    public function setApplication(ApplicationBase $app)
    {
        parent::setApplication($app);
    }
    
    /**
     * 构造函数,初始化
     * @param void
     * @return void
     */
    protected function __construct()
    {
        $this->_data = [
            'cookie' => $_COOKIE,
            'request' => $_REQUEST,
            'post' => $_POST,
            'get' => $_GET,
            'files' => $_FILES
        ];
        $this->_server = $_SERVER;
        $sessionName = ini_get('session.name');
        $sessionId = $_COOKIE[$sessionName];
        unset($_SERVER,$_REQUEST, $_COOKIE, $_POST, $_GET, $_FILES);
        if (isset($sessionId))
        {
            $_COOKIE[$sessionName] = $sessionId;
        }
    }
    
    /**
     * 获取客户端IP
     * @param void
     * @return string $clientIp
     */
    protected function _getIp($server)
    {
        if ($server['HTTP_X_FORWARDED_FOR'])
        {
            return $server['HTTP_X_FORWARDED_FOR'];
        }
        if (isset($server['HTTP_CLIENT_IP']))
        {
            return $server['HTTP_CLIENT_IP'];
        }
        return $server['REMOTE_ADDR'];
    }

    /**
     * 获取完整URL
     * @param void
     * @return string
     */
    protected function _getUrl($server)
    {
        $http = 443 == $server['SERVER_PORT'] ? 'https://' : 'http://';
        $port = (443 == $server['SERVER_PORT'] || 80 == $server['SERVER_PORT']) ? '' : ':' . $server['SERVER_PORT'];
        return $http . $server['HTTP_HOST'] . $port . $server["REQUEST_URI"];
    }
}
?>