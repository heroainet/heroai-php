<?php
/**
 *
 * @Copyright (C), 2011-, King.$i
 * @Name  HttpCookie.php
 * @Author  King
 * @Version  Beta 1.0
 * @Date: Mon Dec 19 00:14 52 CST 2011
 * @Description  HttpCookie 操纵CooKie类
 * @Class List
 *  	1.HttpCookie
 *  @Function List
 *   1.
 *  @History
 *      <author>    <time>                        <version >   <desc>
 *        King      Mon Dec 19 00:14:52 CST 2011  Beta 1.0           第一次建立该文件
 *
 */
namespace Tiny\Mvc\Web;

/**
 * Cookie
 * 
 * @package Web
 * @since : Mon Dec 19 00:15 53 CST 2011
 * @final : Mon Dec 19 00:15 53 CST 2011
 */
class HttpCookie implements \ArrayAccess, \Iterator,\Countable
{

    /**
     * 单例模式
     * 
     * @var self
     */
    protected static $_instance;

    /**
     * cookie
     * 
     * @var array
     */
    protected $_cookies;

    /**
     * cookie域名
     * 
     * @var string
     * @access protected
     */
    protected $_domain = null;

    /**
     * 过期时间
     * 
     * @var int
     * @access protected
     */
    protected $_expires = 360000;

    /**
     * cookie前缀
     * 
     * @var string
     * @access protected
     */
    protected $_prefix = '';

    /**
     * cookie作用路径
     * 
     * @var string
     * @access protcted
     */
    protected $_path = '/';

    /**
     * 是否编码
     * 
     * @var bool
     */
    protected $_isEncode = false;

    /**
     * 获取单一实例
     * 
     * 
     * @param void
     * @return self
     */
    public static function getInstance($cookies = null)
    {
        if (! self::$_instance)
        {
            self::$_instance = new self($cookies);
        }
        return self::$_instance;
    }

    /**
     * 构造函数
     * 
     * @param array $cookies
     * @return void
     */
    protected function __construct(array $cookies)
    {
        $this->_cookies = $cookies;
    }

    /**
     * 设置cookie的作用域
     * 
     * @param string $domain
     * @return void
     */
    public function setDomain($domain)
    {
        $this->_domain = $domain;
    }

    /**
     * 设置默认过期时间
     * 
     * @param int $ex 过期秒数
     * @return void
     */
    public function setExpires($ex)
    {
        $this->_expires = $ex;
    }

    /**
     * 设置域名前缀
     * 
     * @param string $pf
     * @return void
     */
    public function setPrefix($pf)
    {
        $this->_prefix = $pf;
    }

    /**
     * cookie的作用路径
     * 
     * @param string $path
     * @return void
     */
    public function setPath($path)
    {
        $this->_path = $path;
    }

    /**
     * 设置是否编码
     * 
     * @param bool $isEncode 是否编码
     * @return void
     */
    public function setEncode($isEncode)
    {
        $this->_isEncode = (bool) $isEncode;
    }

    /**
     * 获取 COOKIE 数据
     * 
     * @param string $name 域名称,如果为空则返回整个 $COOKIE 数组
     * @param boolean $decode 是否自动解密,如果 set() 时加密了则这里必需要解密,并且解密只能针对单个值
     * @return mixed
     */
    public function get($name = null)
    {
        $name = $this->_prefix . $name;
        $value = $name ? $this->_cookies[$name] : $this->_cookies;
        if ($this->_isEncode)
        {
            $value = $this->_decode($value);
        }
        return $value;
    }

    /**
     * 设置COOKIE
     * 
     * @param string $name COOKIE名称
     * @param string $value 值
     * @param int $time :有效时间,以秒为单位 0 表示会话期间内
     * @param string $domain 域名
     * @param boolean $encode 是否加密
     * @return bool
     */
    public function set($name, $value, $time = null, $path = null, $domain = null)
    {
        $name = $this->_prefix . $name;
        $path = $path ?: $this->_path;
        $domain = $domain ?: $this->_domain;
        $time = (int) $time ?: $this->_expires;
        $time = $time + time();
        if ($this->_isEncode)
        {
            $value = $this->_encode($value);
        }
        return setcookie($name, $value, $time, $path, $domain);
    }

    /**
     * 删除 COOKIE
     * 
     * @access ：public
     * @param string $name COOKIE名称
     * @return void
     */
    public function remove($name)
    {
        $this->set($name, null, - 86400 * 365);
    }

    /**
     * 清除 COOKIE
     * 
     * 
     * @param void
     * @return void
     */
    public function clean()
    {
        foreach ($this->_cookies as $key => $val)
        {
            $this->remove($key);
        }
    }

    /**
     * 实现接口之获取
     * 
     * 
     * @param string $key
     * @return void
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * 实现接口之设置
     * 
     * 
     * @param string $key 键
     * @param string $value 值 其他值均为默认值
     * @return
     *
     */
    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * 实现接口之是否存在
     * 
     * 
     * @param string $key 键
     * @return bool
     */
    public function offsetExists($key)
    {
        return isset($this->_cookies[$key]);
    }

    /**
     * 实现接口之移除cookie
     * 
     * 
     * @param string $key cookie的键
     * @return bool
     */
    public function offsetUnset($key)
    {
        return $this->remove($key);
    }

    /**
     * Iterator rewind
     * 
     * @param void
     * @return
     *
     */
    public function rewind()
    {
        return reset($this->_cookies);
    }

    /**
     * Iterator current
     * 
     * @param void
     * @return
     *
     */
    public function current()
    {
        $current = current($this->_cookies);
        if ($this->_isEncode)
        {
            $current = $this->_decode($current);
        }
        return $current;
    }

    /**
     * Iterator next
     * 
     * @param void
     * @return
     *
     */
    public function next()
    {
        return next($this->_cookies);
    }

    /**
     * Iterator key
     * 
     * @param void
     * @return
     *
     */
    public function key()
    {
        return key($this->_cookies);
    }

    /**
     * Iterator valid
     * 
     * @param void
     * @return
     *
     */
    public function valid()
    {
        return key($this->_cookies) !== null;
    }

    /**
     * 输出字符
     *
     * @param void
     * @return string
     */
    public function __toString()
    {
        return var_export($this->_cookies, true);
    }

    /**
     * 获取总计
     *
     * @param void
     * @return int
     */
    public function count()
    {
        return count($this->_cookies);
    }
    /**
     * 私有方法：加密 COOKIE 数据
     * 
     * @access protected
     * @param string $string
     * @return string
     */
    protected function _encode($value)
    {
        if (! is_array($value))
        {
            $value = base64_encode($value);
            $search = array('=','+','/');
            $replace = array('_','-','|');
            return str_replace($search, $replace, $value);
        }
        $data = [];
        foreach ($value as $key => $val)
        {
            $data[$key] = $this->_encode($val);
        }
        return $data;
    }

    /**
     * 私有方法：解密 COOKIE 数据
     * 
     * @access protected
     * @param $string
     * @return string
     */
    protected function _decode($value)
    {
        if (! is_array($value))
        {
            $replace = array('=','+','/');
            $search = array('_','-','|');
            $str = str_replace($search, $replace, $value);
            return base64_decode($str);
        }
        $data = [];
        foreach ($value as $key => $val)
        {
            $data[$key] = $this->_decode($val);
        }
        return $data;
    }
}
