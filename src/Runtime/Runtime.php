<?php

/**
 * @Copyright (C), 2013-, King.
 * @Name Runtime.php
 * @Author King
 * @Version Beta 1.0
 * @Date: 2019年11月12日上午10:07:58
 * @Description 运行时库
 * @Class List 1.
 * @Function List 1.
 * @History King 2019年11月12日上午10:07:58 第一次建立该文件
 *                 King 2019年11月12日上午10:07:58 修改
 *
 */
namespace Tiny\Runtime;

use Tiny\Mvc\ApplicationBase;

/* 定义框架所在路径 */
define("FRAMEWORK_PATH", dirname(__DIR__) . DIRECTORY_SEPARATOR);

/**
 * 运行时错误
 *
 * @package class
 * @since 2019年11月18日下午3:22:53
 * @final 2019年11月18日下午3:22:53
 */
class RuntimeException extends \Exception
{

}

/**
 * 运行时类
 *
 * @package Runtime
 * @since 2019年11月12日上午10:11:41
 * @final 2019年11月12日上午10:11:41
 */
class Runtime
{

    /**
     * 框架所在目录
     *
     * @var string
     */
    const FRAMEWORK_PATH = FRAMEWORK_PATH;

    /**
     * WEB模式
     * @var integer
     */
    const RUNTIME_MODE_WEB = 0;

    /**
     * 命令行模式
     * @var integer
     */
    const RUNTIME_MODE_CONSOLE = 1;

    /**
     * RPC模式
     * @var integer
     */
    const RUNTIME_MODE_RPC = 2;

    /**
     * 可以自定义的运行时环境参数
     * @var array
     */
    const CUSTOM_ENVS = [
        'RUNTIME_CACHE_ENABLE',
        'RUNTIME_CACHE_SIZE'
    ];

    /**
     * 环境参数类
     * @var Environment 环境参数类
     */
    public $env;

    /**
     * 单例
     * @var Runtime
     */
    protected static $_instance;


    /**
     * app策略集合
     *
     * @var Array
     */
    protected static $_appMap = [
        self::RUNTIME_MODE_CONSOLE => '\Tiny\Mvc\ConsoleApplication',
        self::RUNTIME_MODE_WEB => '\Tiny\Mvc\WebApplication',
        self::RUNTIME_MODE_RPC => '\Tiny\Mvc\RPCApplication'
    ];


    /**
     * 初始化的runtime参数
     * @var array
     */
    protected static $_defENV = [
        'RUNTIME_MODE' => self::RUNTIME_MODE_WEB,
        'RUNTIME_MODE_CONSOLE' => self::RUNTIME_MODE_CONSOLE,
        'RUNTIME_MODE_WEB' => self::RUNTIME_MODE_WEB,
        'RUNTIME_MODE_RPC' => self::RUNTIME_MODE_RPC,
    ];

    /**
     * 应用程序实例
     *
     * @var \Tiny\Mvc\ApplicationBase
     */
    protected $_app;

    /**
     *  缓存句柄
     *
     * @var Cache
     */
    protected $_cache = NULL;

    /**
     * 自动加载库对象
     *
     * @var Autoloader
     */
    protected $_autoloader;

    /**
     * 错误处理实例
     * @var ExceptionHandler
     */
    protected $_exceptionHandler;

    /**
     * 注册的tick回调函数
     * @var array callable
     */
    protected $_tickHooks = [];

    /**
     * 缓存hooks
     * @var array 缓存hooks
     */
    protected $_cacheHooks = [];

    /**
     * @param array $env
     * @return array
     */
    public static function setENV(array $env)
    {
        foreach($env as $ename => $evar)
        {
            if (in_array($ename, self::CUSTOM_ENVS))
            {
                self::$_defENV[$ename] = $evar;
            }
        }
    }

    /**
     * 注册或者替换已有的Application
     * @param int $mode
     * @param string $className
     * @return void
     */
    public static function regApplicationMap($mode, $className)
    {
        if ($className instanceof ApplicationBase)
        {
            self::$_appMap[$mode] = $className;
        }
    }

    /**
     * @获取单例实例
     *
     * @return Runtime
     */
    public static function getInstance($env = NULL)
    {
        if (!self::$_instance)
        {
            self::$_instance = new self($env);
        }
        return self::$_instance;
    }

    /**
     * 根据运行环境创建应用实例， Web \Tiny\Web\Application Console \Tiny\Console\Application RPC \Tiny\Rpc\Application
     *
     * @param $apppath string
     *            app目录所在路径
     * @param $profile string
     *            应用实例的配置路径
     * @return \Tiny\Mvc\ApplicationBase 当前应用实例
     * @example \Tiny::createApplication($apppath, $profile);
     */
    public function createApplication($appPath, $profile = null)
    {
        if (! $this->_app)
        {
            $className = self::$_appMap[$this->env['RUNTIME_MODE']];
            $this->_app = new $className($appPath, $profile);
        }
        return $this->_app;
    }

    /**
     * 导入类库
     *
     * @param string $path
     *            类库加载绝对路径
     * @param string $namespace
     *            命名空间
     * @return false || true
     */
    public function import($path, $namespace = null)
    {
        return $this->_autoloader->add($path, $namespace);
    }

    /**
     * 注册异常处理句柄
     *
     * @param
     *            IExceptionHandler 错误处理句柄接口
     * @return bool
     */
    public function regExceptionHandler(IExceptionHandler $handler)
    {
        return $this->_exceptionHandler->regExceptionHandler($handler);
    }

    /**
     * 注册tick钩子函数
     * @param callable $callback
     * @param array $args
     */
    public function regTickHook(callable $callback, $args = NULL)
    {
        $this->_tickHooks[] = [$callback, $args];
    }

    /**
     * 注册缓存钩子
     *
     * @param callable $cacheHandler
     */
    public function regCacheHook(int $cacheId, callable $cacheHandler)
    {
        return $this->_cache->regCacheHook($cacheId, $cacheHandler);
    }

    /**
     * 触发tick钩子
     */
    public function __destruct()
    {
        $this->tick();
    }

    /**
     * 执行tickhooks
     */
    public function tick()
    {
        foreach($this->_tickHooks as $tick)
        {
            call_user_func($tick[0]);
        }
    }

    /**
     * 构建基本运行环境所需的各种类
     *
     * @return void
     */
    protected function __construct($env)
    {
        $this->env = Environment::getInstance(self::$_defENV);
        $this->_cache = new Cache($this->env['SCRIPT_FILENAME'],$this->env['RUNTIME_MODE'], $this->env['RUNTIME_CACHE_SIZE'], $this->env['RUNTIME_CACHE_TTL'], $this->env['RUNTIME_CACHE_ENABLE']);
        $this->regTickHook(array($this->_cache, 'checkCacheUpdated'));

        $this->_autoloader = Autoloader::getInstance();
        $this->_autoloader->add(self::FRAMEWORK_PATH, 'Tiny');
        $this->regCacheHook($this->env['RUNTIME_CACHE_ID_AUTOLOADER'], [$this->_autoloader, 'oncache']);

        //$this->_autoloader
        $this->_exceptionHandler = ExceptionHandler::getInstance();

        if ($this->env['RUNTIME_MODE'] == $this->env['RUNTIME_MODE_CONSOLE'])
        {
            declare(ticks = 10);
            register_tick_function(array($this, 'tick'));
        }
    }
}

/**
 * 自动加载基类
 *
 * @package Tiny\Runtime
 * @since 2019年11月12日上午10:15:05
 * @final 2019年11月12日上午10:15:05
 */
class Autoloader implements ICacheHandler
{
    /**
     * 单例
     * @var Runtime
     */
    protected static $_instance;

    /**
     * 库
     * @var array
     */
    protected $_libs = [];

    /**
     * 加载路径的数组
     * @var array
     */
    protected $_paths;

    /**
     * @获取单例实例
     *
     * @return Autoloader
     */
    public static function getInstance()
    {
        if (! self::$_instance)
        {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * 缓存事件 ICacheHandler
     * @param array $data
     */
    public function oncache($data = NULL)
    {
        if (is_array($data))
        {
            $this->_paths = $data;
        }
        return $this->_paths;
    }

    /**
     * 添加组件库
     *
     * @param string $path 组件库路径 为*时，添加全局类
     * @param string $prefix 命名空间名称
     * @return void
     */
    public function add($path, $namespace = null)
    {
        if (!$namespace)
        {
            $namespace = basename($path);
        }
        elseif ('*' == $namespace)
        {
            return set_include_path(get_include_path() . PATH_SEPARATOR . $path);
        }
        $this->_libs[$namespace] = $path;
    }

    /**
     * 根据类名加载文件
     *
     * @param string $className 类名
     * @return bool
     */
    public function load($cname)
    {
        if (false === strpos($cname, "\\"))
        {
            return include_once($cname . '.php');
        }

        if (isset($this->_paths[$cname]))
        {
            include $this->_paths[$cname];
            return TRUE;
        }

        $params = explode("\\", $cname);
        $searchParams = array();
        for ($i = count($params); $i >= 1; $i--)
        {
            $searchParams[] =  array(join('\\', array_slice($params, 0, $i)), join('/', array_slice($params, $i)));
        }
        foreach ($searchParams as $sp)
        {
            if ($this->_loadPath($sp[0], $sp[1], $cname))
            {
                break;
            }
        }
    }

    /**
     * 寻找和加载类路径
     *
     * @param string $namespace 寻找的命名空间
     * @param string $pathSuffix 路径尾缀
     * @param string $cname 类名
     * @return bool false加载失败 || true成功
     */
    protected function _loadPath($namespace, $pathSuffix, $cname)
    {
        if (!$this->_libs[$namespace])
        {
            return FALSE;
        }
        $ipath = $this->_libs[$namespace] . $pathSuffix . '.php';

        if (!is_file($ipath))
        {
            if (!$pathSuffix)
            {
                return FALSE;
            }
            $parentDir = dirname($ipath);
            $ipath = $parentDir . DIRECTORY_SEPARATOR . basename($parentDir) . '.php';
            if (!is_file($ipath))
            {
                return FALSE;
            }
        }
        include_once ($ipath);
        if(class_exists($cname) || interface_exists($cname))
        {
            $this->_paths[$cname] = $ipath;
            return TRUE;
        }
        return FALSE;
    }

    /**
     * 构造函数，主动自动加载类
     *
     * @param void
     * @return void
     */
    protected function __construct()
    {
        spl_autoload_register(array($this , 'load'));
    }
}

/**
 * 缓存句柄接口
 *
 */
interface ICacheHandler
{
    public function oncache($data = NULL);
}

/**
 * 文件缓存
 *
 * @package Tiny.Runtime
 * @since ：Mon Nov 14 00 08 38 CST 2011
 * @final :Mon Nov 14 00 08 38 CST 2011
 */
final class Cache
{
    /**
     * 默认缓存策略
     *
     * @var array
     */
    const POLICY_DEFAULT = [
        'TTL' => 60, //默认缓存时间
        'CACHE_ENABLE' => TRUE,
        'CACHE_ID' => '0',
        'CACHE_ID_DEFAULT' => '0',
        'CACHE_SIZE_MIN' => 1000000,
        'CACHE_SIZE' => 10000000,
        'CACHE_HEAD_LENGTH' => 10,
        'CACHE_TYPE_SHMOP' => 0,  //内存缓存模式
        'CACHE_TYPE_FILE' => 1,   //文件缓存模式
    ];

    /**
     * 是否缓存
     * @var bool
     */
    protected $_cacheEnable = TRUE;

    /**
     * 缓存大小
     * @var int
     */
    protected $_cacheSize;

    /**
     * 缓存ID
     * @var int
     */
    protected $_cacheID = NULL;

    /**
     * 缓存类型
     * @var integer
     */
    protected $_cacheType = 0;

    /**
     * 缓存标识路径
     * @var string
     */
    protected $_cachePath;

    /**
     * 缓存标识ID
     * @var int
     */
    protected $_cacheProjectId;

    /**
     * 缓存数组
     * @var array
     */
    protected $_cacheData = NULL;

    /**
     * 是否缓存
     * @var string
     */
    protected $_cacheUpdated = FALSE;

    /**
     * 缓存到期时间
     * @var integer
     */
    protected $_ttl = 0;

    /**
     * 缓存实例数组
     * @var
     */
    protected $_cacheHandlers = [];

    /**
     * 资源操作句柄 shmid
     * @var resource
     */
    protected $_shmID = NULL;

    /**
     * 初始化路径
     *
     * @param $policy array 代理的策略数组
     * @return void
     * @example
     *          new CacheHandler(['cache_path' => __DIR__, 'ttl' => 60, 'cache_size' => 1000000]);
     *          cache_path 固定的文件缓存路径标识
     *          ttl 默认的缓存过期时间
     *          cache_size 缓存初始化大小 字节计算
     *
     */
    public function __construct($cachePath, $cacheId, int $cacheSize, int $ttl = 0, $isEnable  = TRUE)
    {
        if (!file_exists($cachePath))
        {
            throw new RuntimeException(sprintf('runtime cache error:%s is not exists', $cachePath));
        }
        $this->_ttl = $ttl;

        if (!$this->_ttl)
        {
            $this->_ttl  = self::POLICY_DEFAULT['TTL'];
        }

        if ($cacheSize < self::POLICY_DEFAULT['CACHE_SIZE_MIN'])
        {
            $cacheSize = self::POLICY_DEFAULT['CACHE_SIZE_MIN'];
        }

        $this->_cachePath = $cachePath;
        $this->_cacheSize = $cacheSize;
        $this->_cacheProjectId = (int)$cacheId;
        $this->_cacheEnable = (bool)$isEnable;
        if (extension_loaded('shmop'))
        {
            $this->_cacheType = self::POLICY_DEFAULT['CACHE_TYPE_SHMOP'];
            $this->_cacheID = ftok($this->_cachePath, $this->_cacheProjectId);
        }
        else
        {
            $this->_cacheType = self::POLICY_DEFAULT['CACHE_TYPE_FILE'];
            $this->_cacheID = sys_get_temp_dir() . '/' . md5($this->_cacheProjectId . $this->_cachePath);
        }
        if (!$this->_cacheEnable)
        {
            return;
        }
        $this->_readFromCache();
    }

    /**
     * 注册cache钩子 并第一次注入缓存数组
     *
     * @param int $cacheId
     * @param callable $cacheHandler
     */
    public function regCacheHook(int $cacheId, callable $cacheHandler)
    {
        if (!$this->_cacheEnable)
        {
            return FALSE;
        }
        if ($this->_cacheHandlers[$cacheId])
        {
            return FALSE;
        }
        $this->_cacheHandlers[$cacheId] = $cacheHandler;
        $ret = call_user_func_array($cacheHandler, [$this->_cacheData[$cacheId]]);
        return $ret;
    }

    /**
     * 检测是否有缓存更新
     */
    public function checkCacheUpdated()
    {
        if (!$this->_cacheEnable)
        {
            return FALSE;
        }
        foreach($this->_cacheHandlers as $id => $handler)
        {
            $cacheData = $this->_cacheData[$id];
            $tdata = call_user_func($handler);
            if ($cacheData != $tdata)
            {
                $this->_cacheUpdated = TRUE;
                $this->_cacheData[$id] = $tdata;
            }
        }
        if ($this->_cacheUpdated)
        {
            $this->_cacheUpdated = FALSE;
            $this->_writeToCache();
        }
    }


    /**
     * 设置缓存变量
     *
     * @param $key string 键
     * @param $value  mixed 值
     * @param $life int 生命周期
     * @return bool
     */
    public function set($key, $value)
    {
        $this->_cacheData[$key] = $value;
        $this->_cacheUpdated = TRUE;
    }

    /**
     * 获取缓存变量
     *
     * @param $key string || array 为数组时可一次获取多个变量
     * @return bool;
     */
    public function get(int $key)
    {
        return $this->_cacheData[$key];
    }

    /**
     * 从缓存读取内容
     * @return string|NULL|mixed
     */
    protected function _readFromCache()
    {
        $this->_cacheData = [];

        $content = $this->_cacheType == self::POLICY_DEFAULT['CACHE_TYPE_SHMOP']
           ? $this->_readByShmop()
           : $this->_readByFile();

        $data = unserialize($content);
        if(!$data)
        {
                return;
        }

        if($data['ttl'] < time())
        {
            return;
        }
        $this->_cacheData = $data['data'];
    }

    /**
     * 通过shmop读取缓存
     * @return string
     */
    protected function _readByShmop()
    {
        $content = '';
        $shmid = shmop_open($this->_cacheID, 'c', 0644, $this->_cacheSize);
        $headLength = self::POLICY_DEFAULT['CACHE_HEAD_LENGTH'];
        $contentLength = (int)shmop_read($shmid, 0, $headLength);
        if($contentLength > $headLength)
        {
            $content = shmop_read($shmid, $headLength, $contentLength - $headLength);
        }
        shmop_close($shmid);
        return $content;
    }

    /**
     * 通过文件读取缓存
     * @return string
     */
    protected function _readByFile()
    {
        $content = '';
        if (!is_file($this->_cacheID))
        {
            return $content;
        }
        return file_get_contents($this->_cacheID);
    }

    /**
     * 通过shmop方式写入缓存
     * @param string $body
     * @return number
     */
    protected function _writeByShmop($body)
    {
        $ret = 0;
        $strlen = self::POLICY_DEFAULT['CACHE_HEAD_LENGTH'] + strlen($body);
        $head = str_pad($strlen, self::POLICY_DEFAULT['CACHE_HEAD_LENGTH'], 0, STR_PAD_LEFT);
        $fd = fopen($this->_cachePath, 'r');
        if (flock($fd, LOCK_EX|LOCK_NB))
        {
            $shmid = shmop_open($this->_cacheID, 'c', 0644, $this->_cacheSize);
            $content = $head . $body;
            $ret = shmop_write($shmid, $content, 0);
            flock($fd, LOCK_UN);
            shmop_close($shmid);
        }
        fclose($fd);
        return $ret;
    }

    /**
     * 写入到文件
     * @param string $body
     * @return number
     */
    protected function _writeByFile($body)
    {
        return file_put_contents($this->_cacheID, $body, LOCK_EX|LOCK_NB);
    }

    /**
     * 写入数据到缓存
     */
    protected function _writeToCache()
    {
        if (!$this->_cacheEnable)
        {
            return;
        }
        $time = (time() + $this->_ttl);
        $data = ['ttl' => $time, 'data' => $this->_cacheData];
        $body = serialize($data);
        $ret = $this->_cacheType == self::POLICY_DEFAULT['CACHE_TYPE_SHMOP']
                ? $this->_writeByShmop($body)
                : $this->_writeByFile($body);
        return $ret;
    }
}


/**
 * 提供有关当前环境和平台的信息以及操作它们的方法。此类不能被继承。
 *
 * @package Tiny
 * @since 2013-3-30下午12:27:47
 * @final 2013-11-26下午
 * @example
 */
final class Environment implements \ArrayAccess
{
    /**
     * 默认的环境配置函数数组
     *
     * @var array
     */
    const ENV_DEFAULT = [
        'FRAMEWORK_PATH' => FRAMEWORK_PATH,
        'FRAMEWORK_NAME' => 'Tiny Framework For PHP',
        'FRAMEWORK_VERSION' => '1.0.3',

        'RUNTIME_MODE' => -1,
        'RUNTIME_MODE_CONSOLE' => -1,
        'RUNTIME_MODE_WEB' => -1,
        'RUNTIME_MODE_RPC' => -1,

        'RUNTIME_CACHE_ENABLE' => TRUE,
        'RUNTIME_CACHE' => TRUE,
        'RUNTIME_CACHE_SIZE' => 10000000,
        'RUNTIME_CACHE_TTL' => 60,

        'RUNTIME_CACHE_ID_SELF' => 0,
        'RUNTIME_CACHE_ID_RUNTIME' => 1,
        'RUNTIME_CACHE_ID_AUTOLOADER' => 2,
        'RUNTIME_CACHE_ID_CONFIG' => 3,
        'RUNTIME_CACHE_ID_MODEL' => 4
    ];

    /**
     * 单例
     * @var Runtime
     */
    protected static $_instance;

    /**
     * 环境参数列表
     *
     * @var array
     */
    protected $_envdata  = [];

    /**
     * @获取单例实例
     *
     * @return Environment
     */
    public static function getInstance(array $env = NULL)
    {
        if (! self::$_instance)
        {
            self::$_instance = new self($env);
        }
        return self::$_instance;
    }

    /**
     * 获取环境参数
     * @param string $varname
     * @return mixed
     */
    public function get($varname)
    {
        if (!key_exists($varname, $this->_envdata))
        {
            switch ($varname)
            {
                case 'OS_PID':
                    $val = getmypid();
                    break;
                case 'OS_GID':
                    $val = getmygid();
                    break;
                case 'OS_UID':
                    $val = getmyuid();
                    break;
                case 'OS_SYSTEM_NAME':
                    $val = php_uname('s');
                    break;
                case 'OS_HOSTNAME':
                    $val = php_uname('n');
                    break;
                case 'OS_SYSTME_VERSION_NAME':
                    $val = php_uname('r');
                    break;
                case 'OS_SYSTEM_VERSION_INFO':
                    $val = php_uname('v');
                    break;
                case 'OS_MACHINE_TYPE':
                    $val = php_uname('m');
                    break;
                case 'SCRIRT_DIR':
                    $ifiles = get_included_files();
                    $val = dirname($ifiles[0]);
                case 'RUNTIME_MEMORY_SIZE':
                    return memory_get_usage();

                case 'RUNTIME_DEBUG_BACKTRACE':
                    return debug_backtrace();

                case 'SCRIPT_FILENAME':
                    $ifiles = get_included_files();
                    $val = basename($ifiles[0]);
                default:
                    $val = getenv($varname);
            }
            $this->_envdata[$varname] = $val;
        }
        return $this->_envdata[$varname];
    }

    /**
     *
     * @param string $varname
     * @param string || int
     *
     * @return bool
     */
    public function set($varname, $val = NULL)
    {
        if (is_array($varname))
        {
            foreach ($varname as $k => $v)
            {
                $this->_envdata[$k] = $v;
            }
            return;
        }
        $this->_envdata[$varname] = $val;
    }

    /**
     * 获取环境参数
     * @param string $varname
     * @return mixed
     */
    public function offsetGet($varname)
    {
        return $this->get($varname);
    }

    /**
     * 设置环境参数  只读 设置报错
     * @param string
     * @param string $value
     */
    public function offsetSet($offset, $value)
    {
        return;
    }

    /**
     * 销毁  只读 调用报错
     * @param string $offset
     */
    public function offsetUnset($offset)
    {
        return;
    }

    /**
     * 是否有类似这个环境参数
     *
     * @param string $offset
     */
    public function offsetExists($offset)
    {
        return isset($this->_envdata);
    }

    /**
     * @DESC 缓存单例初始化系统参数
     *  * 魔术方法获取 OS_PID
 * OS_GID
 * OS_UID
 * OS_SYSTEM_NAME
 * OS_HOSTNAME
 * OS_SYSTME_VERSION_NAME
 * OS_SYSTEM_VERSION_INFO
 * OS_MACHINE_TYPE
 * RUNTIME_SCRIRT_DIR
 * RUNTIME_MEMORY_SIZE
 * RUNTIME_DEBUG_BACKTRACE
 * RUNTIME_SCRIPT_FILENAME
     *
     */
    protected function __construct(array $env)
    {
        $env = array_merge($_SERVER, $_ENV, self::ENV_DEFAULT, $env);
        if ('cli' == php_sapi_name())
        {
            $env['RUNTIME_MODE'] = $env['RUNTIME_MODE_CONSOLE'];
        }
        if ('FRPC_POST' == $_POST['FRPC_METHOD'] || 'FRPC_POST' == $_SERVER['REQUEST_METHOD'])
        {
            $env['RUNTIME_MODE'] = $env['RUNTIME_MODE_RPC'];
        }

        $env['PHP_VERSION'] = PHP_VERSION;
        $env['PHP_VERSION_ID'] = PHP_VERSION_ID;
        $env['PHP_OS'] = PHP_OS;
        //unset($_ENV, $_SERVER);
        $this->_envdata = $env;
    }
}

/**
 * 异常注册接口
 *
 * @author king
 */
interface IExceptionHandler
{
    /**
     * 异常发生事件触发
     *
     * @param \Exception 异常实例
     * @param $exceptions array 异常数组
     * @return void
     */
    public function onException($exception, $exceptions);
}

/**
 * MVC异常处理
 *
 * @package Tiny
 * @since : 2013-3-22上午06:15:37
 * @final : 2017-3-22上午06:15:37
 */
class ExceptionHandler
{
    /**
     * 错误名集合
     *
     * @var array
     *
     */
    const _errorTypes = array(
        0 => 'Fatal error' ,
        E_ERROR => 'ERROR' ,
        E_WARNING => 'WARNING' ,
        E_PARSE => 'PARSING ERROR' ,
        E_NOTICE => 'NOTICE' ,
        E_CORE_ERROR => 'CORE ERROR' ,
        E_CORE_WARNING => 'CORE WARNING' ,
        E_COMPILE_ERROR => 'COMPILE ERROR' ,
        E_COMPILE_WARNING => 'COMPILE WARNING' ,
        E_USER_ERROR => 'USER ERROR' ,
        E_USER_WARNING => 'USER WARNING' ,
        E_USER_NOTICE => 'USER NOTICE' ,
        E_STRICT => 'STRICT NOTICE' ,
        E_RECOVERABLE_ERROR => 'RECOVERABLE ERROR'
    );

    /**
     * 单例
     *
     * @var self
     */
    protected static $_instance;

    /**
     * 需要抛出异常的错误级别数组
     *
     * @var array
     *
     */
    protected $_throwErrorTypes = array(
        E_ERROR ,
        E_PARSE ,
        E_CORE_ERROR ,
        E_USER_ERROR ,
        E_RECOVERABLE_ERROR ,
        0 ,
    );

    /**
     * 所有异常情况集合
     *
     * @var array
     *
     */
    protected $_exceptions = array();

    /**
     * 注册的异常处理句柄
     *
     * @var array
     *
     */
    protected $_exceptionHandlers = array();

    /**
     * 获取单例
     *
     * @param void
     * @return ExceptionHandler
     */
    public static function getInstance()
    {
        if (! self::$_instance)
        {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * 初始化异常捕获句柄
     *
     *
     * @param void
     * @return void
     */
    protected function __construct()
    {
        set_exception_handler(array($this ,'onException'));
        set_error_handler(array($this ,'onError'));
    }

    /**
     * 注册异常处理触发事件
     *
     *
     * @param $handler IExceptionHandler 完成异常处理接口的函数
     * @return void
     */
    public function regExceptionHandler(IExceptionHandler $handler)
    {
        $this->_exceptionHandlers[] = $handler;
    }

    /**
     * 错误触发时调用的函数
     *
     * @param ……
     * @return void
     */
    public function onError($errno, $errstr, $errfile, $errline)
    {
        if ($errno == E_NOTICE || $errno == 2048)
        {
            return;
        }
        $exception = array(
            'level' => $errno ,
            'message' => $errstr ,
            'file' => $errfile ,
            'line' => $errline ,
            'handler' => 'Exception' ,
            'isThrow' => $this->isThrowError($errno)
        );
        $this->_exceptions[] = $exception;
        if (! $this->_exceptionHandlers)
        {

            return $this->_throwException($exception);
        }
        foreach ($this->_exceptionHandlers as $handler)
        {
            $handler->onException($exception, $this->_exceptions);
        }
    }

    /**
     * 产生异常时调用的函数
     *
     *
     * @param \Exception $exception 异常对象
     * @return void
     */
    public function onException($e)
    {
        $level = $e->getCode();
        if ($level == E_NOTICE || $level == 2048)
        {
            return;
        }
        $exception = array(
            'level' => $level ,
            'message' => $e->getMessage() ,
            'handler' => get_class($e) ,
            'line' => $e->getLine() ,
            'file' => $e->getFile() ,
            'isThrow' => $this->isThrowError($level)
        );

        $this->_exceptions[] = $exception;
        if (!$this->_exceptionHandlers)
        {
            return $this->_throwException($exception);
        }

        foreach ($this->_exceptionHandlers as $handler)
        {
            $handler->onException($exception, $this->_exceptions);
        }
    }

    /**
     * 获取所有异常信息数组
     *
     * @param void
     * @return array
     */
    public function getExceptions()
    {
        return $this->_exceptions;
    }

    /**
     * 获取错误类型名称
     *
     * @param int $level 错误级别
     * @return string
     */
    public function getErrorType($level)
    {
        return isset($this->_errorTypes[$level]) ? $this->_errorTypes[$level] : $this->_errorTypes[0];
    }

    /**
     * 是否是需要抛出异常的错误级别
     *
     * @param int $errno 错误级别
     * @return bool
     */
    public function isThrowError($errno)
    {
        return in_array($errno, $this->_throwErrorTypes);
    }

    /**
     * 默认的抛出异常和错误函数
     *
     * @param $exception string 最新一次异常
     * @return void
     */
    protected function _throwException($exception)
    {
        if (! $exception['isThrow'])
        {
            return;
        }
        foreach ($this->_exceptions as & $e)
        {
            echo '<b>', $this->getErrorType($e['level']), '</b>: "', $e['message'], '" <b>File</b>:"', $e['file'], '" on line <b>', $e['line'], '</b>';
        }
        exit(1);
    }
}
?>