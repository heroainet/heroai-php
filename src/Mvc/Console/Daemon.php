<?php 
/**
 * @Copyright (C), 2013-, King.
 * @Name Daemon.php
 * @Author King
 * @Version Beta 1.0 
 * @Date 2017年4月7日下午8:41:15
 * @Desc
 * @Class List 
 * @Function List 
 * @History King 2017年4月7日下午8:41:15 0 第一次建立该文件
 *               King 2017年4月7日下午8:41:15 1 上午修改
 */
namespace Tiny\Mvc\Console;

use Tiny\Mvc\ConsoleApplication;
/**
 * 命令行守护进程
 *
 * @package 
 * @since 2017年4月7日下午8:41:41
 * @final 2017年4月7日下午8:41:41
 */
class Daemon {
    
    
    /**
     * 委托的应用实例
     *
     * @var 
     */
    protected $_application;
    
    /**
     * 日志记录ID
     *
     * @var 
     */
    protected $_logid;
    
    /**
     * 主进程ID
     * @var int
     * @access protected
     */
    protected $_pid = 0;
    
    /**
     * 需要创建的子进程总量
     * @var int
     * @access protected
     */
    protected $_maxChildren = 0;
    
    /**
     * 挂起时间间隔
     * @var int
     * @access protected
     */
    protected $_timeTrick = 1;
    
    /**
     * PID文件路径
     * @var string
     * @access protected
     */
    protected $_pidFile;
    
    /**
     * 最大运行次数
     * @var void
     * @access protected
     */
    protected $_maxRequest = 1024;
    
    /**
     * 应用实例集合
     * @var array
     * @access protected
     */
    protected $_apps = array ();
    
    /**
     * 当前的子进程总数
     * @var int
     * @access protected
     */
    protected $_childTotal = 0;
    
    /**
     * 当前的子进程总数
     * @var int
     */
    protected $_action = 'start';
    
    /**
     * 当前允许的动作数组
     */
    protected $_allowActions = ['start', 'stop', 'restart'];

    /**
     * 构造化
     *
     * @param ConsoleApplication $app
     * @return void
     */
    public function __construct(ConsoleApplication $app)
    {
        $this->_application = $app;
        $this->_checkHelp($app);
        if (!$app->request->param['daemons'])
        {
            throw new DaemonException("守护模式下，必须以main/index=1 main/i=1方式设置子进程数目");
        }        
        
        $pidDir = $app->properties['daemon.pid_dir'];
        $pid = $app->request->param['pid'] ?: $app->properties['daemon.pid'];
        $trick = $app->request->param['t'] ?: $app->properties['daemon.trick'];
        $logid = $app->request->param['l'] ?: $app->properties['daemon.logid'];
        $maxRequest = $app->request->param['max_request'] ?: $app->properties['daemon.max_request'];
        
        $this->_setTrick($trick);
        $this->_setLogId($logid);
        $this->_setPidFile($pidDir, $pid);
        $this->_setMaxRequest($maxRequest);
        
        foreach ($app->request->param['daemons'] as $d)
        {
            $this->_addChildren($d['c'], $d['a'], $d['n']);
        } 
        
        $action = $app->request->param[0];
        if (!$action)
        {
        	return;
        }
        
        if (!in_array($action, $this->_allowActions))
        {
        	throw new DaemonException("action " . $action . "is not  in [" . join(',' ,$this->_allowActions) .  "]");
        }
        $this->_action = $action;
        
    }
    
    protected function _checkHelp($app)
    {
        if ($app->request->param['h'] || $app->request->param['help'])
        {
            echo "守护进程参数设置:
	-h                       输出帮助文件
	--daemon                 开启守护进程
	-l                       = 日志记录ID
	-t                       = 每次运行子进程循环的停顿时间或重建子进程间隔时间
	-pid                     = PID文件名 注意： 仅文件名
	-max_request             = 每个子进程循环执行的最大请求数
	main/index=2 main/test=2 控制器/动作=数量 可设置多个 
	start/stop/restart        执行的动作函数 开始/停止/重新开始
";
            $this->_end();
        }
    }
    
    /**
     * 设置子进程最大允许执行次数 防止内存泄露。
     * 
     * @param int $num
     * @return void
     */
    protected function _setMaxRequest($num)
    {
        $this->_maxRequest = abs($num) ?: 1;
    }
    
    /**
     * 设置日志文件路径
     * 
     * @param string　$logFile 日志文件路径
     * @return void
     */
    protected function _setLogid($logId)
    {
        $this->_logid = $logId ?: 'tiny_daemon';
    }
    
    /**
     * 设置PID文件路径
     * 
     * @param string $pidFile PID文件路径
     * @return
     */
    protected function _setPidFile($pidDir, $pid)
    {
        if ($pid == "" || !is_dir($pidDir))
        {
            throw new \Exception("Daemon Error:properties.daemon.pid_dir必须为相对目录");
        }        
        $this->_pidFile = $pidDir . $pid;
    }
    
    /**
     * 挂起进程秒数
     * 
     * @param int $time
     * @return bool
     */
    protected function _setTrick($time)
    {
        $this->_timeTrick = ($time <= 1) ? 1 : (int)$time;
    }
    
    /**
     * 设置子进程数目
     *
     * @param
     * @return
     */
    protected function _addChildren($cName, $aName, $num)
    {
        $key = $cName . '|' . $aName;
        $this->_apps[$key] = array('key' => $key, 'cname' => $cName, 'aname' => $aName, 'num' => $num,'pids' => array());
        $this->_maxChildren += $num;
    }
    
    /**
     * 守护运行应用程序实例
     * 
     * @param void
     * @return bool
     */
    public function run()
    {  
         $pid = pcntl_fork();
         if ($pid > 0)
         {
             exit(0);
         }
         
         if ($pid == -1)
         {
              return $this->_end('错误:无法创建子进程!');
         }
         

        declare(ticks=1);
        pcntl_signal(SIGINT, array($this,"signal"));
        pcntl_signal(SIGTSTP, array($this,"signal"));
        
       
        if ('start' == $this->_action)
        {
        	$this->start();
        }
        elseif('stop' == $this->_action)
        {
        	$this->stop();
        }
        
    }
    
    public function stop()
    {
    	if (!$this->_isRunning())
    	{
    		echo "pid file [" . $this->_pidFile . "] does not exist. Not running?\n";
    		exit(1);
    	}
    	
    	$this->_delPid();
    }
    
    public function start()
    {
    	if ($this->_isRunning())
    	{
    		echo "pid file ' . $this->_pidFile . ' already exists, is it already running?\n";
    		exit(3);
    	}
    	
    	$this->_pid = posix_getpid();
    	file_put_contents($this->_pidFile, $this->_pid, LOCK_EX);
    	$this->_log("创建父进程:" . $this->_pid);
        while ($this->_isRunning())
       {
            $app = $this->_getAppDetail();
            
            $pid = pcntl_fork();
            if ($pid == - 1)
            {
                return $this->_end('错误:无法创建子进程!');
            }
            
            if ($pid)
            {
                $this->_log("创建子进程:" . $pid);
                $this->_processTotal++;
                $this->_apps[$app['key']]['pids'][$pid] = $pid;
                $this->_keepChild($pid);
            }
            
            if (!$pid)
            {
            	$this->_dispathChildren($app['cname'], $app['aname']);
            }
            
       }
    }
        
    /**
     * 获取appName
     * 
     * @param void
     * @return string
     */
    protected function _getAppDetail()
    {
        foreach ($this->_apps as $app)
        {
            if ($app['num'] > count($app['pids']))
            {
                return $app;
            }
        }
    }
    
    /**
     * 执行子进程的APP实例
     * @access protected
     * @param string $appName app名称
     * @param string $profile 配置文件路径
     * @return void
     */
    protected function _dispathChildren($cName, $aName)
    {
        for ($i = 0; $i < $this->_maxRequest; $i++)
        {
            if (!$this->_isRunning())
            {
            	$this->_log('子进程 ' . posix_getpid() . '退出...');
            	exit(1);
            }
            $this->_log('子进程 ' . posix_getpid() . ' ' . $this->_maxRequest . ' ' . $i);
            ob_start();
            $this->_application->dispatch($cName, $aName);
            $content = ob_get_contents();
            $this->_log($content, 6);
            ob_end_clean();
            sleep($this->_timeTrick);
        }
        
        $this->_log('子进程 ' . posix_getpid() . ' 自动退出 最大 ' . $this->_maxRequest . ' ');
        exit(0);
    }
    
    /**
     * 守护子进程
     * @access protected
     * @param int $pid 新生的子进程PID
     * @return void
     */
    protected function _keepChild()
    {
        if ($this->_processTotal < $this->_maxChildren)
        {
            return;
        }
        $pid = pcntl_wait($status);
        $this->_processTotal--;
        foreach ($this->_apps as $appNname => $app)
        {
            if ($app['pids'][$pid])
            {
                unset($this->_apps[$appNname]['pids'][$pid]);
                sleep($this->_timeTrick);
                return;
            }
        }
    }
    
    /**
     * 通过输入参数初始化守护进程
     * @access protected
     * @param void
     * @return void
     */
    protected function _getPid()
    {
        if (!is_file($this->_pidFile))
        {
            return;
        }
        return (int)file_get_contents($this->_pidFile);
    }
    
    /**
     * 是否正在运行中
     */
    protected function _isRunning()
    {
    	$pid = $this->_getPid();
    	if (!$pid)
    	{
    		return false;
    	}
    	return file_exists('/proc/' . $pid);
    }
    

    /**
     * 删除PID文件 如果有
     * @param void
     * @return void
     */
    protected function _delPid()
    {	
    	$pid = $this->_getPid();
    	if ($pid)
    	{
    		posix_kill($pid, SIGINT); 
    	}
    	echo 'aaa';
    	if (is_file($this->_pidFile))
    	{
    		@unlink($this->_pidFile);
    	}
    }
    
    /**
     * 接受退出信号后
     *
     * @param
     * @return
     */
    public function signal($s)
    {
        $this->_end("Get signal and exit");
    }
    
    /**
     * 结束主进程
     * @access protected
     * @param string $log 退出时的日志
     * @return void
     */
    protected function _end($log = '')
    {
        if ($log)
        {
            $this->_log($log, 3);
        }
        exit(0);
    }
    
    /**
     * 写入日志文件
     * @access protected
     * @param string $log 日志内容
     * @return void
     */
    protected function _log($log, $priority = 5)
    {
        $this->_application->getLogger()->log($this->_logid, $log, $priority);
    }
    
}
?>