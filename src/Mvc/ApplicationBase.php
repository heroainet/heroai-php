<?php
/**
 * @Copyright (C), 2013-, King.
 * @Name Base.php
 * @Author King
 * @Version Beta 1.0
 * @Date 2017年3月8日下午4:04:15
 * @Desc
 * @Class List
 * @Function List
 * @History King 2017年3月8日下午4:04:15 0 第一次建立该文件
 *               King 2017年3月8日下午4:04:15 1 上午修改
 */
namespace Tiny\Mvc;

use Tiny\Runtime\IExceptionHandler;
use Tiny\Config\Configuration;
use Tiny\Tiny;
use Tiny\Log\Logger;
use Tiny\Cache\Cache;
use Tiny\Data\Data;
use Tiny\Lang\Lang;
use Tiny\Mvc\Router\IRouter;
use Tiny\Mvc\Controller\Controller;
use Tiny\Mvc\Viewer\Viewer;
use Tiny\Mvc\Plugin\Iplugin;
use Tiny\Mvc\Bootstrap\Base as BootstrapBase;
use Tiny\Mvc\Router\Router;
use Tiny\Mvc\Controller\Base;
use Tiny\Runtime\Runtime;
use Tiny\Runtime\Environment;
use Tiny\Filter\IFilter;
use Tiny\Filter\Filter;

/**
 * app实例基类
 *
 * @author King
 * @package Tiny
 * @since 2013-3-21下午04:55:41
 * @final 2017-3-11下午04:55:41
 */
class ApplicationBase implements IExceptionHandler
{

    /**
     * 应用实例的事件集合
     *
     * @var array
     */
    const PLUGIN_EVENTS = array(
        'onbeginrequest' ,
        'onendrequest' ,
        'onrouterstartup' ,
        'onroutershutdown' ,
        'onpredispatch' ,
        'onpostdispatch' ,
        'onexception'
    );

    /**
     * APP所在的目录路径
     *
     * @var string
     *
     */
    public $path;

    /**
     * App配置文件路径
     *
     * @var string
     *
     */
    public $profile;

    /**
     * 是否为调试模式
     *
     * @var bool true | false
     */
    public $isDebug = false;

    /**
     * 默认语言
     *
     * @var string
     */
    public $charset = 'zh_cn';

    /**
     * 默认时区
     *
     * @var string
     */
    public $timezone = 'PRC';

    /**
     *
     * @var Runtime
     */
    public $runtime;

    /**
     * 运行时参数
     * @var Environment
     */
    public $env;
    /**
     * public
     *
     * @var Configuration App的基本配置类
     *
     */
    public $properties;

    /**
     * 当前请求实例
     *
     * @var string WebRequest
     *
     */
    public $request;

    /**
     * 当前响应实例
     *
     * @var string WebResponse
     *
     */
    public $response;

    /**
     * 当前路由器
     *
     * @var IRouter
     */
    public $router;

    /**
     * 当前执行的控制器实例
     *
     * @var Controller
     */
    public $controller;


    /**
     * 引导类
     *
     * @var BootStrapBase
     *
     */
    protected $_bootstrap;

    /**
     * 路由器实例
     *
     * @var Router
     */
    protected $_router;

    /**
     * 配置实例
     *
     * @var Configuration
     */
    protected $_config;

    /**
     * 缓存实例
     *
     * @var Cache
     */
    protected $_cache;

    /**
     * 设置数据池实例
     *
     * @var Data
     */
    protected $_data;

    /**
     * 语言包实例
     *
     * @var Lang
     */
    protected $_lang;

    /**
     * 日志实例
     *
     * @var Logger
     */
    protected $_logger;

    /**
     * 视图实例
     *
     * @var Viewer
     */
    protected $_viewer;

    /**
     * 过滤
     * @var \Tiny\Filter\Filter
     */
    protected $_filter;

    /**
     * 控制器实例数组
     *
     * @var Controller
     */
    protected $_controllers = [];

    /**
     * 模型实例数组
     *
     * @var \Tiny\Mvc\Model\Base
     */
    protected $_models = [];

    /**
     * 默认的命名空间
     *
     * @var string
     */
    protected $_namespace = '';

    /**
     * 控制器命名空间
     *
     * @var string
     */
    protected $_cNamespace;

    /**
     * 模型命名空间
     *
     * @var string
     */
    protected $_mNamespace;

    /**
     * 应用程序运行的时间戳
     *
     * @var int timeline
     */
    protected $_startTime = 0;

    /**
     * Application注册的插件
     *
     * @var array
     *
     */
    protected $_plugins = array();

    /**
     * 配置数组
     *
     * @var Array
     */
    protected $_prop;

    /**
     * model加载类名缓存
     * @var array
     */
    protected $_modelPathList = NULL;

    /**
     * 初始化应用实例
     *
     * @param string $profile 配置文件路径
     * @return void
     */
    public function __construct($path, $profile = null)
    {
        if (!Tiny::getApplication())
        {
            Tiny::setApplication($this);
        }
        $this->runtime = Runtime::getInstance();
        $this->env = $this->runtime->env;


        $this->path = $path;
        if (! $profile)
        {
            $profile = $path . DIRECTORY_SEPARATOR . 'profile.php';
        }
        $this->profile = $profile;
        $this->_startTime = microtime(true);
        $this->_init();
    }

    /**
     * 设置引导类
     *
     * @param BootstrapBase $bootStrap 继承了BootstrapBase的引导类实例
     * @return void
     */
    public function setBootstrap(BootstrapBase $bootStrap)
    {
        $this->_bootstrap = $bootStrap;
        return $this;
    }

    /**
     * 设置配置实例
     *
     * @param Configuration $config 配置实例
     * @return Configuration
     */
    public function setConfig(Configuration $config)
    {
        $this->_config = $config;
    }

    /**
     * 设置路由器
     *
     * @param Router 路由器
     * @return self
     */
    public function setRouter(Router $router)
    {
        $this->_router = $router;
        return $this;
    }

    /**
     *
     * @param 获取路由器
     * @return Router
     *
     */
    public function getRouter()
    {
        if (! $this->_router)
        {
            $this->_router = new Router($this->request);
        }
        return $this->_router;
    }

    /**
     * 获取app实例的配置实例
     *
     * @param void
     * @return Configuration
     */
    public function getConfig()
    {
        if ($this->_config)
        {
            return $this->_config;
        }

        $prop = $this->_prop['config'];
        if (! $prop['enabled'])
        {
            throw new ApplicationException("properties.config.enabled is false!");
        }
        $this->_config = new Configuration($prop['path']);
        $this->runtime->regCacheHook($this->env['RUNTIME_CACHE_ID_CONFIG'], [$this, 'onConfigCache']);
        return $this->_config;
    }

    /**
     * 触发配置缓存检测事件
     * @param array $data
     * @return array
     */
    public function onConfigCache($data = NULL)
    {
        if (is_array($data))
        {
            $this->_config->setData($data);
        }
        return $this->_config->getData();
    }

    /**
     * 设置缓存实例
     *
     * @param Cache 缓存实例
     * @return self
     */
    public function setCache(Cache $cache)
    {
        $this->_cache = $cache;
        return $this;
    }

    /**
     * 获取应用实例的缓存对象
     *
     * @param void
     * @return Cache
     */
    public function getCache()
    {
        if ($this->_cache)
        {
            return $this->_cache;
        }
        $prop = $this->_prop['cache'];
        if (! $prop['enabled'])
        {
            throw new ApplicationException("properties.cache.enabled is false!");
        }

        $this->_cache = Cache::getInstance();
        $prop['drivers'] = $prop['drivers'] ?: array();
        $prop['policys'] = $prop['policys'] ?: array();
        foreach ($prop['drivers'] as $type => $className)
        {
            Cache::regDriver($type, $className);
        }
        foreach ($prop['policys'] as $policy)
        {
            $policy['lifetime'] = $policy['lifetime'] ?: $prop['lifetime'];
            $policy['path'] = $policy['path'] ?: $prop['path'];
            $this->_cache->regPolicy($policy);
        }
        return $this->_cache;
    }

    /**
     * 设置数据池实例
     *
     * @param Data
     * @return self
     */
    public function setData(Data $data)
    {
        $this->_data = $data;
        return $this;
    }

    /**
     * 获取数据库连接池
     *
     * @param void
     * @return Data
     */
    public function getData()
    {
        if ($this->_data)
        {
            return $this->_data;
        }
        $prop = $this->_prop['data'];
        if (! $prop['enabled'])
        {
            throw new ApplicationException("properties.data.enabled is false!");
        }
        $this->_data = Data::getInstance();
        $prop['drivers'] = $prop['drivers'] ?: array();
        $prop['policys'] = $prop['policys'] ?: array();
        $prop['charset'] = $prop['charset'] ?: 'utf8';
        foreach ($prop['drivers'] as $type => $className)
        {
            Data::regDriver($type, $className);
        }
        foreach ($prop['policys'] as $policy)
        {
            $policy['def_charset'] = $prop['charset'];
            $this->_data->addPolicy($policy);
        }
        return $this->_data;
    }

    /**
     * 设置应用过滤器
     * @param IFilter $filter
     */
    public function setFilter(IFilter $filter)
    {
        $this->_filter = $filter;
        return $this->_filter;
    }

    /**
     * 获取过滤器
     *
     * @throws ApplicationException
     * @return \Tiny\Filter\Filter
     */
    public function getFilter()
    {
        if ($this->_filter)
        {
            return $this->_filter;
        }
        $prop = $this->_prop['filter'];
        if (! $prop['enabled'])
        {
            return NULL;
        }

        $this->_filter = Filter::getInstance();
        if ($this->env['RUNTIME_MODE'] == $this->env['RUNTIME_MODE_WEB'] && $prop['web'])
        {
            $this->_filter->addFilter($prop['web']);
        }
        if ($this->env['RUNTIME_MODE'] == $this->env['RUNTIME_MODE_CONSOLE'] && $prop['console'])
        {
            $this->_filter->addFilter($prop['console']);
        }
        if ($this->env['RUNTIME_MODE'] == $this->env['RUNTIME_MODE_RPC'] && $prop['rpc'])
        {
            $this->_filter->addFilter($prop['rpc']);
        }
        if (is_array($prop['filters']))
        {
            foreach($prop['filters'] as $fname)
            {
                $this->_filter->addFilter($fname);
            }
        }
        return $this->_filter;
    }

    /**
     * 设置语言包实例
     *
     * @param Lang $lang 语言包实例
     * @return self
     */
    public function setLang(Lang $lang)
    {
        $this->_lang = $lang;
        return $this;
    }

    /**
     * 获取语言操作对象
     *
     * @param void
     * @return Lang
     */
    public function getLang()
    {
        if ($this->_lang)
        {
            return $this->_lang;
        }
        $prop = $this->_prop['lang'];
        if (! $prop['enabled'])
        {
            throw new ApplicationException("properties.lang.enabled is false!");
        }
        $this->_lang = Lang::getInstance();
        $this->_lang->setLocale($prop['locale'])->setLangPath($prop['path']);
        return $this->_lang;
    }

    /**
     * 设置日志实例
     *
     * @param Logger 日志实例
     * @return self
     */
    public function setLogger(Logger $logger)
    {
        $this->_logger = $logger;
        return $this;
    }

    /**
     * 获取日志对象
     *
     * @param void
     * @return Logger
     */
    public function getLogger()
    {
        if ($this->_logger)
        {
            return $this->_logger;
        }
        $prop = $this->_prop['log'];
        if (! $prop['enabled'])
        {
            throw new ApplicationException("properties.log.enabled is false!");
        }
        $this->_logger = Logger::getInstance();
        $prop['drivers'] = $prop['drivers'] ?: array();
        foreach ($prop['drivers'] as $type => $className)
        {
            Logger::regWriter($type, $className);
        }
        $policy = ('file' == $prop['type']) ? array('path' => $prop['path']) : array();
        $this->_logger->addWriter($policy, $prop['type']);
        return $this->_logger;
    }

    /**
     * 异常触发事件
     *
     * @param array $exception 异常
     * @param array $exceptions 所有异常
     * @return void
     */
    public function onException($e, $exceptions)
    {
        $isLog = $this->_prop['exception']['log'];
        $logId = $this->_prop['exception']['logid'];
        if ($isLog)
        {
            $logMsg = $e['handle'] . ':' . $e['message'] . ' from ' . $e['file'] . ' on line ' . $e['line'];
            $this->getLogger()->log($logId, $logMsg);
        }
        if ($e['isThrow'])
        {
            $this->onPostDispatch();
            $this->response->output();
        }
    }

    /**
     * 简单获取控制器
     *
     * @param string $cName 模型名称
     * @return Base
     */
    public function getController($cname)
    {
        $cid = $cname;
        if ($this->_controllers[$cid])
        {
            return $this->_controllers[$cid];
        }
        $cname = preg_replace_callback("/\b\w/", function($param) {
            return strtoupper($param[0]);
        }, $cname);

        $cname = "\\" . preg_replace("/\/+/", "\\", $cname);
        $cName = $this->_cNamespace . $cname;
        if (! class_exists($cName))
        {
            throw new ApplicationException("Dispatch errror:controller,{$cName}不存在，无法加载", E_ERROR);
        }

        $this->_controllers[$cid] = new $cName();
        $this->_controllers[$cid]->setApplication($this);
        if (! $this->_controllers[$cid] instanceof \Tiny\Mvc\Controller\Base)
        {
            throw new ApplicationException("Controller:'{$cName}' is not instanceof Tiny\Mvc\Controlller\Controller!", E_ERROR);
        }
        return $this->_controllers[$cid];
    }

    /**
     * 简单获取模型
     *
     * @param string $modelName 模型名称
     * @return \Tiny\Mvc\Model\Base
     */
    public function getModel($mname)
    {
        $mid = strtolower($mname);
        if ($this->_models[$mid])
        {
            return $this->_models[$mid];
        }
        $modelFullName = $this->_searchModel($mname);
        if ($modelFullName)
        {
            $this->_models[$mid] = new $modelFullName();
                return $this->_models[$mid];
        }
    }

    /**
     * 触发模型缓存检测事件
     * @param array $data
     * @return array
     */
    public function onModelCache($data = NULL)
    {
        if (is_array($data))
        {
            $this->_modelPathList = $data;
        }
        return $this->_modelPathList;
    }

    /**
     *
     * @param string $modelName
     */
    protected function _searchModel($mname)
    {
        if (NULL === $this->_modelPathList)
        {
            $this->_modelPathList = [];
            $this->runtime->regCacheHook($this->env['RUNTIME_CACHE_ID_MODEL'], [$this, 'onModelCache']);
        }

        if ($this->_modelPathList[$mname] && class_exists($this->_modelPathList[$mname]))
        {
            return $this->_modelPathList[$mname];
        }
        if (FALSE === strpos($mname, "\\"))
        {
            $mname = preg_replace('/([A-Z]+)/', '\\\\$1', ucfirst($mname));
        }
        $params = explode("\\", $mname);
        for ($i = count($params) ; $i > 0; $i--)
        {
            $modelFullName =  join('\\', array_slice($params, 0, $i - 1)) . '\\' . join('', array_slice($params, $i - 1));

            if ($modelFullName[0] != '\\')
            {
                $modelFullName  = "\\" . $modelFullName;
            }
            $modelFullName = $this->_mNamespace . $modelFullName;
            if (class_exists($modelFullName))
            {
                $this->_modelPathList[$mname] = $modelFullName;
                return $modelFullName;
            }
        }
    }

    /**
     * 设置视图实例
     *
     * @param Viewer 视图实例
     * @return Base
     */
    public function setViewer(Viewer $viewer)
    {
        $this->_viewer = $viewer;
        return $this;
    }

    /**
     * 获取视图类型
     *
     * @param void
     * @return Viewer
     */
    public function getViewer()
    {
        if ($this->_viewer)
        {
            return $this->_viewer;
        }
        $prop = $this->_prop['view'];
        $this->_viewer = Viewer::getInstance();

        $assign = $prop['assign'] ?: array();
        $engines = $prop['engines'] ?: array();

        $assign['env'] = $this->runtime->env;
        $assign['request'] = $this->request;
        $assign['response'] = $this->response;
        $assign['properties'] = $this->properties;

        if ($this->_prop['config']['enabled'])
        {
            $assign['config'] = $this->getConfig();
        }

        if ($this->_prop['lang']['enabled'])
        {
            $assign['lang'] = $this->getLang();
            $this->_viewer->setBasePath($this->_prop['lang']['locale']);
        }

        foreach ($engines as $ext => $ename)
        {
            $this->_viewer->bindEngineByExt($ext, $ename);
        }
        $this->_viewer->setTemplatePath($prop['src']);
        $this->_viewer->setCompilePath($prop['compile']);
        $assign['view'] = $this->_viewer;
        $this->_viewer->assign($assign);
        return $this->_viewer;
    }

    /**
     * 设置默认的时区
     *
     *
     * @param string $timezone 时区标示
     * @return bool
     */
    public function setTimezone($timezone)
    {
        return date_default_timezone_set($timezone);
    }

    /**
     * 获取已经设置的默认时区
     *
     *
     * @param void
     * @return string
     */
    public function getTimezone()
    {
        return date_default_timezone_get();
    }

    /**
     * 注册插件
     *
     *
     * @param Iplugin 实现插件接口的实例
     * @return self
     */
    public function regPlugin(Iplugin $plugin)
    {
        $this->_plugins[] = $plugin;
        return $this;
    }

    /**
     * 执行
     *
     *
     * @param void
     * @return void
     */
    public function run()
    {
        $this->_bootstrap();
        $this->onRouterStartup();
        $this->_route();
        $this->onRouterShutdown();
        $this->_doFilter();
        $this->onPreDispatch();
        $this->dispatch();
        $this->onPostDispatch();
        $this->response->output();
    }


    /**
     * 分发
     *
     * @access protected
     * @param void
     * @return void
     */
    public function dispatch($cName = null, $aName = null)
    {
        $cName = $cName ?: $this->request->getController();
        $aName = $aName ?: $this->request->getAction();
        $aName .= 'Action';
        $controller = $this->getController($cName, $aName);
        $this->controller = $controller;
        $ret = $controller->onBeginExecute($this->request, $this->response);
        if (FALSE === $ret)
        {
            return FALSE;
        }
        if (!method_exists($controller, $aName))
        {
            throw new ApplicationException("Dispatch error: The Action '{$aName}' of Controller '{$cName}' is not exists ");
        }
        $ret = call_user_func_array(array($controller ,$aName), array($this->request ,$this->response));
        $controller->onEndExecute();
        return $ret;
    }

    /**
     * 运行插件
     *
     * @param string $method 插件事件
     * @param $params array 参数
     * @return void
     */
    public function __call($method, $params)
    {
        return $this->_plugin($method, $params);
    }

    /**
     * 保留
     */
    public function __destruct()
    {
        if ($this->_modelPathUpdated && $this->_modelCache)
        {
            $this->_modelCache->set($this->_modelPathList);
        }
    }

    /**
     * 执行过滤
     */
    protected function _doFilter()
    {
       $filter = $this->getFilter();
       if(!$filter)
       {
           return;
       }
       $filter->doFilter($this->request, $this->response);
    }

    /**
     * 执行初始化
     *
     * @param void
     * @return void
     */
    protected function _init()
    {
        $this->_initResponse();
        $this->_initConfig();
        $this->_initNamespace();
        $this->_initDebug();
        $this->_initImport();
        $this->_initException();
        $this->_initRequest();

    }

    /**
     * 初始化应用程序的配置对象
     *
     * @param void
     * @return void
     */
    protected function _initConfig()
    {
        $this->properties = new Configuration($this->profile);
        $this->_initPath($this->properties['path']);
        $this->_prop = $this->properties->get();
        $prop = $this->_prop;
        $this->_namespace = $prop['app']['namespace'];
        if (isset($prop['timezone']))
        {
            $this->timezone = $prop['timezone'];
            $this->setTimezone($prop['timezone']);
        }

        if (isset($prop['charset']))
        {
            $this->charset = $prop['charset'];
        }
    }

    /**
     * 初始化命名空间
     *
     * @param void
     * @return void
     */
    protected function _initNamespace()
    {
        $this->_namespace = $this->_prop['app']['namespace'] ?: 'App';
        $cprefix = $this->_prop['controller']['namespace'];
        if (static::class == 'Tiny\Mvc\ConsoleApplication')
        {
            $cprefix = $this->_prop['controller']['console'];
        }
        elseif (static::class == 'Tiny\Mvc\RPCApplication')
        {
            $cprefix = $this->_prop['controller']['rpc'];
        }

        $this->_cNamespace = '\\' . $this->_namespace . '\\' . $cprefix;
        $this->_mNamespace = '\\' . $this->_namespace . '\\' . $this->_prop['model']['namespace'];
    }

    /**
     * 初始化debug插件
     *
     * @param void
     * @return void
     */
    protected function _initDebug()
    {
        if ($this->properties['debug'])
        {
            $this->isDebug = true;
            $this->regPlugin(new \Tiny\Mvc\Plugin\Debug($this));
        }
    }

    /**
     * 初始化异常处理
     *
     * @param void
     * @return void
     */
    protected function _initException()
    {
        if ($this->properties['exception.enable'])
        {
            $this->runtime->regExceptionHandler($this);
        }
    }

    /**
     * 初始化路径
     *
     * @param 初始化路径
     * @return void
     *
     */
    protected function _initPath(array $paths)
    {
        foreach ($paths as $p)
        {
            $this->properties[$p] = $this->path . $this->properties[$p];
        }
    }

    /**
     * 初始化加载类库
     *
     * @param void
     * @return void
     */
    protected function _initImport()
    {
        $runtime = Runtime::getInstance();
        $runtime->import($this->_prop['src']['path'], $this->_namespace);
        if (!is_array($this->_prop['imports']))
        {
        	return;
        }
        foreach($this->_prop['imports'] as $ns => $p)
        {
            $runtime->import($this->properties[$p], $ns);
        }
    }

    /**
     * 初始化请求
     *
     * @param void
     * @return void
     */
    protected function _initRequest()
    {
        if (! $this->request)
        {
            return;
        }

        $this->request->setApplication($this);
        $prop = $this->_prop;
        $this->request->setController($prop['controller']['default']);
        $this->request->setControllerParam($prop['controller']['param']);
        $this->request->setAction($prop['action']['default']);
        $this->request->setActionParam($prop['action']['param']);
    }

    /**
     * 初始化响应
     *
     * @param void
     * @return void
     */
    protected function _initResponse()
    {
        $this->response->setApplication($this);
        $this->response->setLocale($this->properties['lang']['locale']);
        $this->response->setCharset($this->properties['charset']);
    }

    /**
     * 通过魔法函数触发插件的事件
     *
     *
     * @param string $method 函数名称
     * @param array $params 参数数组
     * @return void
     */
    protected function _plugin($method, $params)
    {
        $method = strtolower($method);
        if (! in_array($method, static::PLUGIN_EVENTS))
        {
            return;
        }

        foreach ($this->_plugins as $plugin)
        {
            $params[] = $this;
            call_user_func_array(array($plugin ,$method), $params);
        }
    }

    /**
     * 引导
     *
     * @access protected
     * @param void
     * @return void
     */
    protected function _bootstrap()
    {
        if ($this->_bootstrap)
        {
            $this->_bootstrap->bootstrap($this);
        }
    }

    /**
     * 执行路由
     *
     * @access protected
     * @param void
     * @return void
     */
    protected function _route()
    {
        $prop = $this->_prop['router'];
        if (! $prop['enabled'])
        {
            return;
        }
        $routers = $prop['routers'] ?: array();
        $rules = $prop['rules'] ?: array();
        $router = $this->getRouter();

        foreach ($routers as $k => $r)
        {
            $router->regDriver($k, $r);
        }
        foreach ($rules as $rule)
        {
            $router->addRule($rule['router'], $rule['rule'], $rule);
        }
        $router->route();

    }
}
?>