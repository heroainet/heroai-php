<?php
/**
 * @Copyright (C), 2013-, King.
 * @Name Logger.php
 * @Author King
 * @Version 1.0 
 * @Date: 2013-12-10上午02:27:22
 * @Description 日志记录者
 * @Class List 
 * @Function 
 * @History <author> <time> <version > <desc> 
 king 2013-12-10上午02:27:22  1.0  第一次建立该文件
 */
namespace Tiny\Log;

use Tiny\Log\Writer\IWriter;

/**
 * 日志记录前端类
 * 
 * @package Tiny.Log
 * @since 2013-12-10上午02:27:54
 * @final 2013-12-10上午02:27:54
 */
class Logger
{

    /**
     * 日志级别
     * 
     * @var int
     */
    const EMERG = 0;

    const ALERT = 1;

    const CRIT = 2;

    const ERR = 3;

    const WARN = 4;

    const NOTICE = 5;

    const INFO = 6;

    const DEBUG = 7;

    /**
     * 错误对应的日志级别
     * 
     * @var array
     */
    protected static $_errorPriorityMap = array(
        E_NOTICE => self::NOTICE ,
        E_USER_NOTICE => self::NOTICE ,
        E_WARNING => self::WARN ,
        E_CORE_WARNING => self::WARN ,
        E_USER_WARNING => self::WARN ,
        E_ERROR => self::ERR ,
        E_USER_ERROR => self::ERR ,
        E_CORE_ERROR => self::ERR ,
        E_RECOVERABLE_ERROR => self::ERR ,
        E_STRICT => self::DEBUG ,
        E_DEPRECATED => self::DEBUG ,
        E_USER_DEPRECATED => self::DEBUG   
    );

    /**
     * 优先级别数组
     * 
     * @var array
     */
    protected static $_priorities = array(
        self::EMERG => 'EMERG' ,
        self::ALERT => 'ALERT' ,
        self::CRIT => 'CRIT' ,
        self::ERR => 'ERR' ,
        self::WARN => 'WARN' ,
        self::NOTICE => 'NOTICE' ,
        self::INFO => 'INFO' ,
        self::DEBUG => 'DEBUG'
    );

    /**
     * 日志写入器的注册数组
     * 
     * @var array
     */
    protected static $_writerMap = array(
        'file' => 'Tiny\Log\Writer\File' ,
        'syslog' => 'Tiny\Log\Writer\Syslog' ,
        'rsyslog' => 'Tiny\Log\Writer\Rsyslog'
    );

    /**
     * 单一实例
     * 
     * @var Logger
     */
    protected static $_instance;

    /**
     * 日志写入器的数组
     * 
     * @var array
     */
    protected $_writers = array();

    /**
     * 获取Logger的单一实例
     * 
     * @param void
     * @return Logger
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
     * 注册日志写入器类型
     * 
     * @param string $type 日志写入器类型
     * @param string $className 日志写入器的类名
     * @return void
     */
    public static function regLogWriter($type, $className)
    {
        if (self::$_writerMap[$type])
        {
            throw new LogException("注册日志写入器失败:type:${type}已经存在");
        }
        self::$_writerMap[$type] = $className;
    }

    /**
     * 添加日志的写入代理
     * 
     * @param $priority int 指定的日志级别 可以为数组
     * @return
     *
     */
    public function addWriter($options, $type, $priority = array(0, 1, 2, 3, 4, 5, 6, 7))
    {
        if (! self::$_writerMap[$type])
        {
            throw new LogException("添加日志的写入实例失败：type:${type}不存在");
        }
        $this->_writers[] = array('options' => $options,'type' => $type,'priority' => $priority,'instance' => null);
    }

    /**
     * 日志记录
     * 
     * @param $priority int 日志优先级别
     * @param mixed $message 日志内容
     * @param array $extra 附加信息数组
     * @return void
     */
    public function log($id, $message, $priority = 1, $extra = array())
    {
        if ($priority < 0 || $priority > 7)
        {
            $priority = 7;
        }
        if (is_object($message) || is_array($message))
        {
            $message = var_export($message, true);
        }
        if (! empty($extra))
        {
            $message .= var_export($extra, true);
        }
        $message = self::$_priorities[$priority] . ' ' . date('y-m-d H:i:s') . ' ' . str_replace("\n", '', $message) . "\r\n";
        $this->write((string) $id, $message, $priority);
    }

    /**
     * 记录错误信息
     * 
     * @param int $errLevel 错误优先级别
     * @param mixed $message 日志内容
     * @param array $extra 附加信息数组
     * @return void
     */
    public function error($errLevel, $message, $extra = array())
    {
        return $this->log('error', self::$_errorPriorityMap[$errLevel], $message, $extra);
    }

    /**
     * 写入日志
     * 
     * @param void
     * @return void
     */
    public function write($id, $message, $priority)
    {
        if (count($this->_writers) == 0)
        {
            return;
        }
        foreach ($this->_writers as & $w)
        {
            if (! ($w['priority'] == $priority || (is_array($w['priority']) && in_array($priority, $w['priority']))))
            {
                continue;
            }
            if (! $w['instance'])
            {
                $w['instance'] = new self::$_writerMap[$w['type']]($w['options']);
                if (! ($w['instance'] instanceof IWriter))
                {
                    throw new LogException('实例化LogWriter失败：没有实现接口Tiny\Log\Writer\IWriter');
                }
            }
            $w['instance']->doWrite($id, $message, $priority);
        }
    }

    /**
     * 构造函数
     * 
     * @param void
     * @return void
     */
    protected function __construct()
    {
    }
}
?>