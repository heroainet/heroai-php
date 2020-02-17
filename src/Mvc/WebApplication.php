<?php 
/**
 * @Copyright (C), 2013-, King.
 * @Name WebApplication.php
 * @Author King
 * @Version Beta 1.0 
 * @Date 2017年3月8日下午4:02:43
 * @Desc
 * @Class List 
 * @Function List 
 * @History King 2017年3月8日下午4:02:43 0 第一次建立该文件
 *               King 2017年3月8日下午4:02:43 1 上午修改
 */
namespace Tiny\Mvc;

use Tiny\Mvc\Request\WebRequest;
use Tiny\Mvc\Response\WebResponse;
use Tiny\Mvc\Web\Session\HttpSession;
use Tiny\Mvc\Web\HttpCookie;
/**
 * WEB应用程序实例
 * @author King
 * @package Tiny。MVC
 * @since 2013-3-21下午04:55:41
 * @final 2013-3-21下午04:55:41
 */
class WebApplication extends ApplicationBase
{


    /**
     * cookie对象
     * @var HttpCookie
     * @access protected
     */
    protected $_cookie;

    /**
     * Session对象
     * @var HttpSession
     * @access portected
     */
    protected $_session;

    /**
     * 获取http对话信息
     * 
     * @param void
     * @return HttpSession
     */
    public function getSession()
    {
        if (! $this->_session)
        {
            $this->_session = HttpSession::getInstance($this->_prop['session']);
        }
        return $this->_session;
    }

    /**
     * 获取Cookie对象
     * 
     * @param void
     * @return HttpCookie
     */
    public function getCookie($data = null)
    {
        if (!$this->_cookie)
        {
            $this->_cookie = HttpCookie::getInstance($data);
            $prop = $this->_prop['cookie'];
            $this->_cookie->setDomain($prop['domain']);
            $this->_cookie->setExpires((int)$prop['expires']);
            $this->_cookie->setPrefix((string)$prop['prefix']);
            $this->_cookie->setPath($prop['path']);
            $this->_cookie->setEncode($prop['encode']);
        }
        return $this->_cookie;
    }
    
    /**
     * 初始化请求实例
     * @param void
     * @return void
     */
    protected function _initRequest()
    {
        $this->request =  WebRequest::getInstance();
        parent::_initRequest();
    }
    
    /**
     * 初始化响应实例
     * @param void
     * @return void
     */
    protected  function _initResponse()
    {
        $this->response = WebResponse::getInstance();
        parent::_initResponse();
    }
}
?>