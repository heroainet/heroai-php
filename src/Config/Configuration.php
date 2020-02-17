<?php
/**
 * @Copyright (C), 2011-, King.
 * @Name: Config.php
 * @Author: King
 * @Version: Beta 1.0
 * @Date: 2013-4-5下午12:29:59
 * @Description:
 * @Class List:
 *  	1.
 *  @Function List:
 *   1.
 *  @History:
 *      <author>    <time>                        <version >   <desc>
 *        King      2013-4-5下午12:29:59  Beta 1.0           第一次建立该文件
 *
 */
namespace Tiny\Config;

/**
 * 配置类
 * 
 * @package Tiny.Config
 * @since ：Mon Oct 31 23 54 26 CST 2011
 * @final :Mon Oct 31 23 54 26 CST 2011
 *         2018-02-12 修改与优化类 路径为文件夹下，可以自动寻找下级目录的配置文件
 */
class Configuration implements \ArrayAccess
{
    /**
     * 
     * @var array
     */
    protected static $_fileVars = [];
    
    /**
     * 该配置实例是否为文件模式
     * 
     * @var string
     */
    protected $_isFile = false;

    /**
     * 该配置文件/文件夹的路径
     * @var string
     */
    protected $_path;

    /**
     * 文件名或文件数组
     * 
     * @var string || array
     */
    protected $_files = [];
    
    /**
     * 在配置文件夹中读取的变量数组
     * 
     * @var array
     */
    protected $_data = NULL;
    
    /**
     * 已加载的节点和值
     *
     * @var array
     */
    protected $_nodes = [];

    /**
     * 初始化配置文件路径
     * 
     * @param $cpath string 配置文件或者文件夹路径
     * @return void
     */
    public function __construct($cpath)
    {   
        if (!file_exists($cpath))
        {
            throw new ConfigException('配置实例化错误:路径"' . $cpath . '"不存在!', E_ERROR);
        }
        $this->_path = $cpath;
        $this->_isFile = is_file($cpath);
    }
    
    /**
     * 设置data
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->_data = $data;
    }
    
    /**
     * 获取data
     * @return array
     */
    public function getData()
    {
        return $this->_data ?: [];
    }
     
    /**
     * 获取配置 ,例如 setting.a.b
     * 
     * @param $node string 节点名
     * @return string
     */
    public function get($node = NULL)
    {
        $nodes = $this->_parseNode($node);
        if (NULL === $nodes)
        {
            return $this->_data;
        }
        $data = $this->_data;
        
        foreach ($nodes as $n)
        {
            $data = & $data[$n];
        }
        return $data;
    }
    
    /**
     * 设置
     * 
     * @param $node string 节点设置
     * @param $val string 值
     * @return bool
     */
    public function set($node, $val)
    {
        $nodes = $this->_parseNode($node);
        $ret = & $this->_data;
        foreach ($nodes as $n)
        {
            $ret = & $ret[$n];
        }
        $ret  = $val;
    }

    /**
     * 移除参数
     * 
     * @param $param string 参数名
     * @return void
     */
    public function remove($node)
    {
        return $this->set($node, NULL);
    }

    /**
     * 是否存在某个配置节点
     * 
     * @param string $param
     * @return bool
     */
    public function exists($node)
    {
        return $this->get($node) ? true : false;
    }

    /**
     * ArrayAccess接口必须函数，是否存在
     * 
     * @param $node string 解析参数
     * @return bool
     */
    public function offsetExists($node)
    {
        return $this->exists($node);
    }

    /**
     * ArrayAccess接口必须函数，设置
     * 
     * @param $param string 解析参数
     * @return array || null
     */
    public function offsetSet($key, $value)
    {
        return $this->set($key, $value);
    }

    /**
     * ArrayAccess接口必须函数，获取
     * 
     * @param $param string 解析参数
     * @return array || null
     */
    public function offsetGet($param)
    {
        return $this->get($param);
    }

    /**
     * ArrayAccess接口必须函数 ,移除
     * 
     * @param $param string 解析参数
     * @return array || null
     */
    public function offsetUnset($param)
    {
        return $this->remove($param);
    }
    
    /**
     * 解析参数
     * 
     * @param string $param
     * @return array
     */
    protected function _parseNode($node) 
    {
        $nodes = NULL == $node ? NULL : explode('.', $node);
        if (NULL === $this->_data)
        {
            if ($this->_isFile)
            {
                $this->_data = $this->_loadDataFromFile($this->_path);
            }
            else
            {
                $this->_data = [];
                $this->_getAllDataFromDir($this->_path, $this->_data);
            }
        }
        return $nodes;
    }
        
    /**
     * 一次性获取所有数据 从文件夹
     * @param string $path
     * @param array $d
     * @return void
     */
    protected function _getAllDataFromDir($path, & $d)
    {
        $files = scandir($path);
        $paths  = [];
        foreach($files as $fname)
        {
            if ($fname == '.' || $fname == '..')
            {
                continue;
            }
            $fpath  = $path   . $fname;
            $node = basename($fname, '.php');
            if (is_file($fpath) && substr($fname, -4) == '.php')
            {
                $paths[$node][0] = $fpath;
            }
            elseif (is_dir($fpath))
            {
                $paths[$node][1] = $fpath . '/';
            }
        }
        
        foreach($paths as $node => $path)
        {  
            if ($path[0])
            {   
                $data = $this->_loadDataFromFile($path[0]);
                
                if ($data)
                {
                    if (is_array($d[$node]) && is_array($data))
                    {
                        $d[$node] += $data;
                    }
                    else 
                    {
                        $d[$node] = $data;
                    }
                }
            }
            if($path[1])
            {
                $this->_getAllDataFromDir($path[1], $d[$node]);
            }
        }
    }
    
    /**
     * 根据文件路径获取数据
     *
     * @param array $nodes 节点数组
     * @return NULL or mixed
     */
    protected function _loadDataFromDir($node, $nodes)
    {
        if (NULL === $node || in_array($node, $this->_nodes))
        {
            return;
        }
        if (NULL === $this->_data)
        {
            $this->_data = [];
        }
        $nodeLength = count($nodes);
        for ($i = 1; $i <= $nodeLength; $i++)
        {
            $fnodes = array_slice($nodes, 0, $i);
            $pnodes = array_slice($nodes, $i);
            
            $fpath = $this->_path . join('/', $fnodes) . '.php';
            if (!is_file($fpath))
            {
                continue;
            }
            
            $isExists = $this->_searchDataFromDir($fnodes, $pnodes, $fpath);
            if ($isExists)
            {
                return;
            }
        }
     }
    
     /**
      * 从文件夹中查找所在节点数据
      * @param array 文件节点 $fnodes
      * @param array 父节点 $pnodes
      * @param string $fpath
      * @return boolean
      */
    protected function _searchDataFromDir($fnodes, $pnodes, $fpath)
    {
        $nval = $this->_loadDataFromFile($fpath);
        if (FALSE === $nval)
        {
            return FALSE;
        }
        
        $data = & $this->_data;
        foreach ($fnodes as $n)
        {
            $data = & $data[$n];
        }
        $data = is_array($data) ? array_merge($data, $nval) : $nval;
        foreach ($pnodes as $n)
        {
            if (!isset($data[$n]))
            {
                return FALSE;
            }
            $data = & $data[$n];
        }
        return TRUE;
    }
    
    /**
     * 从文件加载数据
     * 
     * @param string $path
     */
    protected function _loadDataFromFile($path)
    {
        $path = preg_replace('/\/+/', '/', $path);
        if (!isset(self::$_fileVars[$path]))
        {
            self::$_fileVars[$path] = $this->_readConfigFromFile($path);
        }
        return self::$_fileVars[$path];
    }
    
    /**
     * 从文件路径读取数据
     * 
     * @param string $file
     * @return mixed|boolean
     */
    protected function _readConfigFromFile($fpath)
    {
        $rval = include_once($fpath);
        if (is_array($rval))
        {
            return $rval;
        }
        
        $fname = basename($fpath, '.php');
        
        $ival = ${$fname};
        if (is_array($ival))
        {
            return $ival;
        }
        return FALSE;
    }
}
?>