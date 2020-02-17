<?php
/**
 * @Copyright (C), 2011-, King.
 * @Name: Profile.php
 * @Author: King
 * @Version: Beta 1.0
 * @Date: Sat Nov 12 23:16:52 CST 2011
 * @Description:主配置程序
 * @Class List:
 * 1.
 * @Function List:
 * 1.
 * @History:
 * <author> <time> <version > <desc>
 * King Fri Nov 18 00:20:44 CST 2011 Beta 1.0 第一次建立该文件
 */
$profile = array();

/**
 * 基本设置
 */
$profile['debug'] = true;      /*是否开启调试模式: bool false 不开启 | bool true 开启*/


$profile['timezone'] = 'PRC';  /*设置时区*/
$profile['charset'] = 'utf-8'; /*设置编码*/

/**
 * 异常模块
 */
$profile['exception']['enable'] = true;  /*异常处理:bool true 开启异常统一处理|bool false 屏蔽所有异常|string log 异常为日志输出*/
$profile['exception']['log'] = true;     /*是否以日志方式输出*/
$profile['exception']['logid'] = 'tiny_exception.log';

/**
 * 应用设置
 */
$profile['app']['namespace'] = 'App';   /*命名空间*/
$profile['app']['assets'] = 'assets/';  /*资源文件夹*/
$profile['app']['runtime'] = 'runtime/';
$profile['app']['tmp'] = 'runtime/tmp/';

/**
 * 命令行守护进程设置
 */
$profile['daemon']['logid'] = 'tiny_daemon';
$profile['daemon']['trick'] = 2;
$profile['daemon']['pid_dir'] = 'runtime/pid/';
$profile['daemon']['pid'] = 'tiny_daemon';
$profile['daemon']['max_request'] = 1024;
$profile['daemon']['help'] =  '
守护进程参数设置:
    -h/--help       帮助
    -d/--daemon     开启守护进程
    -l              日志记录ID
    -t              重建子进程间隔时间
    main/index=2    控制器/动作=数量 可设置多个
';

/**
 * 源码设置
 */
$profile['src']['path'] = '';             /*源码路径*/
$profile['src']['global'] = 'global/';       /*全局类*/
$profile['src']['library'] = 'library/';       /*实例库*/

$profile['src']['controller'] = 'controllers/web/'; /*控制类*/
$profile['src']['model'] = 'models/';           /*模型类*/
$profile['src']['console'] = 'controllers/console/';        /*命令行控制类*/
$profile['src']['rpc'] = 'controllers/rpc/';               /*rpc控制类*/
$profile['src']['common'] = 'common/';         /*通用类*/
$profile['src']['view'] = 'views/';             /*视图源码*/
$profile['src']['zp'] = dirname(dirname(APPLICATION_PATH)) . "/lib/vendor/zaaap/"; //ZP公共库

/**
 * 配置模块设置
 */
$profile['config']['enabled'] = true; /* 是否开启默认配置模块 */
$profile['config']['path'] = 'config/'; /* 配置文件相对路径 */

/**
 * 语言模块设置
 */
$profile['lang']['enabled'] = true;   /*是否开启 */
$profile['lang']['locale'] = 'zh_cn';
$profile['lang']['path'] = 'lang/';  /*存放语言包的目录 */

/**
 * 日志模块设置
 */
$profile['log']['enabled'] = true;
$profile['log']['type'] = 'file';    /*默认可以设置file|syslog 设置类型为file时，需要设置log.path为可写目录路径 */
$profile['log']['path'] = 'runtime/log/';


/**
 * 数据模块设置
 * id为 default时，即为默认缓存实例
 *  driver mysql
 *  dirver mysqli
 *  dirver pdo_mysql
 *  driver redis
 *  driver memcache
 *  driver ssdb
 */
$profile['data']['enabled'] = true; /* 是否开启数据池 */
$profile['data']['charset'] = 'utf8';
$profile['data']['policys'] = [
    ['id' => 'redis', 'driver' => 'redis', 'host' => '127.0.0.1', 'port' => '6379' ],
    ['id' => 'redis_cache', 'driver' => 'redis', 'host' => '127.0.0.1', 'port' => '6379'],
	['id' => 'redis_session', 'driver' => 'redis', 'host' => '127.0.0.1', 'port' => '6379'],
    ['id' => 'redis_queue', 'driver' => 'redis', 'host' => '127.0.0.1', 'port' => '6379'],
    ['id' => 'default', 'driver' => 'db.mysql_pdo', 'host' => '127.0.0.1', 'port' => '3306', 'user' => 'root', 'password' => '123456', 'dbname' => 'mysql']
];

/**
 * 缓存模块设置
 * id为 default时，即为默认缓存实例 可以用Cache::getInstance()使用 或者在controller以及Model中 直接以$this->cache使用
 * driver 1 FILE缓存  Warnning: 文件缓存填写相对application的路径，不允许绝对路径
 * 类型 2 : memcache缓存
 * 类型 3 ：memory内存缓存
 * 类型 5 Redis缓存 */
$profile['cache']['enabled'] = true; /* 是否默认开启缓存模块，若不开启，则以下设置无效 */
$profile['cache']['lifetime'] = 3600;
$profile['cache']['path'] = 'runtime/cache/'; /* 缓存文件夹相对路径 */
$profile['cache']['policys'] = [
    ['id' => 'default', 'driver' => 'redis', 'lifetime' => 3600, 'dataid' => 'redis_cache']
];

/**
 * HTTP SESSION设置
 * driver 为空 PHP自身Session
 * driver memcache Memcache
 * driver redis Redis作为Session */
$profile['session']['enabled'] = true;
$profile['session']['domain'] = '';
$profile['session']['path'] = '/';
$profile['session']['expires'] = 36000;
$profile['session']['domain'] = '';
$profile['session']['driver'] = 'redis';
$profile['session']['dataid'] = 'redis_session';

/**
 * 过滤器配置
 */
$profile['filter']['enabled'] = true;
$profile['filter']['web'] = '\Tiny\Filter\WebFilter';
$profile['filter']['console'] = '\Tiny\Filter\ConsoleFilter';
$profile['filter']['filters'] = [];

/**
 * HTTP COOKIE设置
 */
$profile['cookie']['domain'] = '';
$profile['cookie']['path'] = '/';
$profile['cookie']['expires'] = 3600;
$profile['cookie']['prefix'] = '';
$profile['cookie']['encode'] = false;

/**
 * 控制器设置
 */
$profile['controller']['default'] = 'main';
$profile['controller']['param'] = 'c';
$profile['controller']['namespace'] = 'Controller';
$profile['controller']['console'] = 'Controller\Console';
$profile['controller']['rpc'] = 'Controller\RPC';


/**
 * 命令行
 */
$profile['console']['namespace'] = 'Console';

/**
 * 模型
 */
$profile['model']['namespace'] = 'Model';
/**
 * 动作设置
 */
$profile['action']['default'] = 'index';
$profile['action']['param'] = 'a';

/**
 * 输出JSON时 默认指定的配置ID
 */
$profile['response']['formatJsonConfigId'] = 'status';

/**
 * 视图设置
 */
$profile['view']['src']     = 'views/';
$profile['view']['cache']   = 'runtime/view/cache/';
$profile['view']['compile'] = 'runtime/view/compile/';
$profile['view']['config']  = 'runtime/view/config/';
/**
 * 视图引擎绑定
 * 通过扩展名绑定解析引擎
 * php PHP原生引擎
 * 类型 tpl Smarty模板引擎
 * 类型 htm Template模板引擎
 */
$profile['view']['engines'] = array();

/**
 * 预先设置的视图变量
 */
$profile['view']['assign'] = array();

/**
 * 路由规则设置
 */
$profile['router']['enabled'] = true; /* 是否开启router */
$profile['router']['routers'] = array(); /*注册自定义的router*/
$profile['router']['rules'] = [
    ['router' => 'pathinfo', 'rule' => ['ext' => '.html'], 'domain' => ''],
   // ['router' => 'static', 'rule' => ['ext' => '.html'], 'domain' => '']
    ];

/**
 * 是否开启插件
 */
$profile['plugin']['enabled'] = false;

/**
 * 需要添加绝对路径的相对路径
 */
$profile['path'] = [
            'src.path',
            'app.assets',
            'app.runtime',
            'config.path',
            'lang.path',
            'log.path',
            'cache.path',
            'view.src',
            'view.cache',
            'view.compile',
            'view.config',
            'src.library',
            'src.global',
			'src.controller',
			'src.console',
			'src.rpc',
			'src.model',
			'src.common',
            'daemon.pid_dir'
];

/**
 * 自动加载库的配置
 */
$profile['imports'] = [
		'App\Controller' => 'src.controller',
		'App\Controller\Console' => 'src.console',
		'App\Controller\Rpc' => 'src.rpc',
		'App\Model' => 'src.model',
		'App\Common' => 'src.common',
		'*' => 'src.global',
        'ZP' => 'src.zp',
];
?>
