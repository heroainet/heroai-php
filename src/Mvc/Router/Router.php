<?php
/**
 * @Copyright (C), 2013-, King.
 * @Name Router.php
 * @Author King
 * @Version Beta 1.0 
 * @Date 2017年3月8日下午4:20:28
 * @Desc
 * @Class List 
 * @Function List 
 * @History King 2017年3月8日下午4:20:28 0 第一次建立该文件
 *               King 2017年3月8日下午4:20:28 1 上午修改
 */
namespace Tiny\Mvc\Router;

use Tiny\Mvc\Request\Base as Request;

/**
 * 路由器主体管理者
 * 
 * @package Router
 * @since : Thu Dec 15 09 22 30 CST 2011
 * @final : Thu Dec 15 09 22 30 CST 2011
 */
class Router
{

    /**
     * 路由驱动类的集合数组
     * 
     * @var array
     */
    protected $_driverMaps = [
        'regex' => '\Tiny\Mvc\Router\RegEx',
        'pathinfo' => '\Tiny\Mvc\Router\PathInfo',
        'static' => '\Tiny\Mvc\Router\StaticPath'
    ];
    
    /**
     * 当前Http应用程序的请求对象
     * 
     * @var Request
     */
    protected $_req;

    /**
     * 路由规则集合
     * 
     * @var array
     */
    protected $_rules = array();

    /**
     * 是否已经执行过路由检测
     * 
     * @var bool
     */
    protected $_isRouted = false;

    /**
     * 匹配的路由规则
     * 
     * @var IRouter
     */
    protected $_matchRule;

    /**
     * 解析的参数
     * 
     * @var array
     */
    protected $_params = array();

    /**
     * 注册路由驱动
     * 
     * @param string $type类型名称
     * @param string $className 路由名称
     * @return bool
     */
    public function regDriver($type, $className)
    {
        if (! $type || isset($this->_driverMaps[$type]))
        {
            return false;
        }
        $this->_driverMaps[$type] = $className;
    }

    /**
     * 构造函数
     * 
     * @param Request $req
     * @return void
     */
    public function __construct(Request $req)
    {
        $this->_req = $req;
    }

    /**
     * 添加路由规则
     * 
     * @param $rule IRouter
     * @return void
     */
    public function addRule($driver, $rule, $ruledata = NULL)
    {
        if (! $this->_driverMaps[$driver])
        {
            return false;
        }
        $rule['className'] = $this->_driverMaps[$driver];
        $rule['ruleData'] = $ruledata;
        $this->_rules[] = $rule;
    }

    /**
     * 执行路由动作
     * 
     * @param void
     * @return void
     */
    public function route()
    {
        foreach ($this->_rules as $r)
        {
            $router = $this->_getRouter($r['className']);
            if ($router->checkRule($r, $this->_req->getRouterString()))
            {
                return $this->resolveRule($router);
            }
        }
        return false;
    }

    /**
     * 解析规则，并注入到当前应用程序的参数中去
     * 
     * @param array $params 参数
     * @return void
     */
    public function resolveRule(IRouter $rule)
    {
        $this->_matchRule = $rule;
        $this->_params = $rule->getParams();  
        $this->_req->setRouterParam($this->_params);
    }

    /**
     * 获取解析Url而来的参数
     * 
     * @param void
     * @return array
     */
    public function getParams()
    {
        return $this->_matchRule ? $this->_params : array();
    }

    /**
     * 获取路由对象
     * 
     * @param array $rule
     * @return string 规则
     */
    protected function _getRouter($className)
    {
        static $routers = [];
        $routerId = strtolower($className);
        
        if (! $routers[$routerId])
        {
            $routers[$routerId] = new $className();
            if (! $routers[$routerId] instanceof IRouter)
            {
                throw new RouterException('router driver:' . $className . ' is not instanceof Tiny\Mvc\Router\IRouter');
            }
        }
        $router = $routers[$routerId];
        return $router;
    }
}
?>
