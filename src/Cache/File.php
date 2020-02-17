<?php
/**
 * @Copyright (C), 2011-, King.
 * @Name  FileCache.php
 * @Author  King
 * @Version  1.0
 * @Date: Sat Nov 12 23 16 52 CST 2011
 * @Description
 * @Class List
 *  	1.File 文件缓存适配器
 *  @History
 *      <author>    <time>                        <version >   <desc>
 *        King      Mon Nov 14 00:08:21 CST 2011  Beta 1.0           第一次建立该文件
 *        King      2013-12-05                       1.0             重新修订该文件
 */
namespace Tiny\Cache;

/**
 * 文件缓存
 * 
 * @package Tiny.Cache
 * @since ：Mon Nov 14 00 08 38 CST 2011
 * @final :Mon Nov 14 00 08 38 CST 2011
 */
class File implements ICache, \ArrayAccess
{

    /**
     * 截取的文件头长度
     * 
     * @var int
     */
    const HEADER_LENGTH = 10;

    /**
     * 缓存文件的扩展名
     * 
     * @var string
     */
    const EXT = '.txt';

    /**
     * 默认的服务器缓存策略
     * 
     * @var array
     * @access protected
     */
    protected $_policy = array('lifetime' => 3600 ,'path' => '');

    /**
     * 初始化路径
     * 
     * @param $policy array 代理的策略数组
     * @return void
     *
     */
    public function __construct(array $policy = array())
    {
        $policy = array_merge($this->_policy, $policy);
        $policy['path'] = rtrim($policy['path'], '\\/');
        if ($policy['path'] == "" || !is_dir($policy['path']))
        {
            throw new CacheException('Cache.File实例化失败：目录' . $policy['path'] . '不是一个已存在的目录');
        }
        $policy['path'] .= DIRECTORY_SEPARATOR;
        $this->_policy = $policy;
    }

    /**
     * 设置缓存
     * 
     * @param string $key 缓存的键 $key为array时 可以批量设置缓存
     * @param mixed $value 缓存的值 $key为array时 为设置生命周期的值
     * @param int $life 缓存的生命周期
     * @return bool
     */
    public function set($key, $value = null, $life = null)
    {
        if (! $key)
        {
            return false;
        }
        if (is_array($key))
        {
            $life = $value;
        }
        $life = is_null($life) ? $this->_policy['lifetime'] : (int) $life;
        if (is_array($key))
        {
            foreach ($key as $k => $v)
            {
                $this->_set($k, $v, $life);
            }
            return true;
        }
        return $this->_set($key, $value, $life);
    }

    /**
     * 获取缓存
     * 
     * @param string || array $key 获取缓存的键名 如果$key为数组 则可以批量获取缓存
     * @return mixed
     */
    public function get($key)
    {
        if (is_array($key))
        {
            $ret = array();
            foreach ($key as $k)
            {
                $ret[$k] = $this->_get($k);
            }
            return $ret;
        }
        return $this->_get($key);
    }

    /**
     * 判断缓存是否存在
     * 
     * @param string $key 键
     * @return bool
     */
    public function exists($key)
    {
        if (null == $key)
        {
            return false;
        }
        return $this->_get($key) ? true : false;
    }

    /**
     * 移除缓存
     * 
     * @param string $key 缓存的键 $key为array时 可以批量删除
     * @return bool
     */
    public function remove($key)
    {
        if (null == trim($key))
        {
            return false;
        }
        $filename = $this->_getFilePath($key);
        if (! is_file($filename))
        {
            return false;
        }
        return unlink($filename);
    }

    /**
     * 清理所有缓存
     * 
     * @param void
     * @return void
     */
    public function clean()
    {
        $path = $this->_policy['path'];
        $dirs = scandir($path);
        foreach ($dirs as $file)
        {
            if (self::EXT == substr($file, - 4))
            {
                unlink($path . DIRECTORY_SEPARATOR . $file);
            }
        }
    }

    /**
     * 数组接口之设置
     * 
     * @param $key string 键
     * @param $value mixed 值
     * @return
     *
     */
    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * 数组接口之获取缓存实例
     * 
     * @param $key string 键
     * @return array
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * 数组接口之是否存在该值
     * 
     * @param $key string 键
     * @return boolean
     */
    public function offsetExists($key)
    {
        return (null == $this->get($key)) ? true : false;
    }

    /**
     * 数组接口之移除该值
     * 
     * @param $key string 键
     * @return void
     */
    public function offsetUnset($key)
    {
        $this->remove($key);
    }

    /**
     * 设置缓存变量
     * 
     * @param $key string 键
     * @param $value  mixed 值
     * @param $life int 生命周期
     * @return bool
     */
    protected function _set($key, $value, $life)
    {
        $header = time() + $life;
        return $this->_writeFile($this->_getFilePath($key), $header . serialize($value));
    }

    /**
     * 获取缓存变量
     * 
     * @param $key string || array 为数组时可一次获取多个变量
     * @return bool;
     */
    protected function _get($key)
    {
        /* 构建文件路径 */
        $filename = $this->_getFilePath($key);
        if (! is_file($filename))
        {
            return null;
        }
        /* 获取文件内容 */
        if (!$fp = fopen($filename, 'r'))
        {
            return null;
        }
        
        flock($fp , LOCK_SH);
        $fsize = filesize($filename);
        if ($fsize)
        {
            $contents = fread($fp , $fsize);
        }
        flock($fp , LOCK_UN);
        fclose($fp);
        if (intval(substr($contents, 0, self::HEADER_LENGTH)) < time())
        {
            unlink($filename);
            return null;
        }
        return unserialize(substr($contents, self::HEADER_LENGTH));
    }

    /**
     * 获取文件缓存路径
     * 
     * @param $key string 键
     * @return string
     */
    protected function _getFilePath($key)
    {
        return $this->_policy['path'] . md5($key) . self::EXT;
    }

    /**
     * 写入文件
     * 
     * @param $filename string 文件路径
     * @param $string string 写入的字符串
     * @return bool
     */
    protected function _writeFile($filename, $string)
    {
        return file_put_contents($filename, $string, LOCK_EX);
    }
}