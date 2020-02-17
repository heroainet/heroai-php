<?php
/**
 * @Copyright (C), 2013-, King.
 * @Name Mysqli.php
 * @Author King
 * @Version 1.0 
 * @Date: 2013-11-28上午06:55:47
 * @Description MYSQL操作类 MYSQLI扩展
 * @Class List 
 * @Function 
 * @History <author> <time> <version > <desc> 
 king 2013-11-28上午06:55:47  1.0  第一次建立该文件
 */
namespace Tiny\Data\Db\Mysql;

use Tiny\Data\Db\IDb;
use Tiny\Data\Data;

/**
 *
 * @package
 *
 * @since 2013-11-28上午06:56:26
 * @final 2013-11-28上午06:56:26
 */
class Mysqli implements IDb
{

    /**
     * 最大重连次数
     *
     * @var int
     */
    const RELINK_MAX = 3;
    
    /**
     * 重连的错误列表
     *
     * @var array
     */
    const RELINK_LIST = [2006, 2013];
    
    /**
     * 配置数组
     * 
     * @var array
     */
    protected $_conf;

    /**
     * 连接标示
     * 
     * @var mixed  null || mysql handle
     */
    protected $_conn;

    /**
     * 重连次数
     * 
     * @var int
     */
    protected $_relink = 0;
    
    /**
     * QUERY返回对象
     *
     * @var string statement
     */
    protected $_statement = FALSE;

    /**
     * 统一的构造函数
     * 
     * @param array $policy 默认为空函数
     * @return
     *
     */
    public function __construct(array $conf = array())
    {
        $this->_conf = $conf;
    }

    /**
     * 触发查询事件
     * 
     * @param string $msg 查询内容
     * @param float $time
     * @return void
     */
    public function onQuery($msg, $time)
    {
        Data::addQuery($msg, $time, __CLASS__);
    }

    /**
     * 错误发生事件
     * 
     * @param void
     * @return void
     */
    public function onError($msg)
    {
        throw new MysqlException('%s %s:%s', $this->_conn->errno, $this->_conn->error, $msg);
    }

    /**
     * 开始连接
     * 
     * @param void
     * @return \Mysqli
     */
    public function getConnector()
    {
        if (!$this->_conn)
        {
            $mtime = microtime(TRUE);
            $conf = $this->_conf;
            $this->_conn = new \Mysqli($conf['host'], $conf['user'], $conf['password'], $conf['dbname'], $conf['port']);
            if ($this->_conn->connect_error)
            {
                $this->onError('连接失败:' . $this->_conn->connect_error);
                return;
            }
            $this->_conn->set_charset($conf['charset']);      
            $this->onQuery(sprintf('连接...%s@%s:%d', $conf['user'], $conf['host'], $conf['port']), microtime(TRUE) - $mtime);
        }
        return $this->_conn;
    }

    /**
     * 获取最近一条错误的内容
     * 
     * @param void
     * @return string
     */
    public function getErrorMSg()
    {
        return $this->getConnector()->error;
    }

    /**
     * 获取最近一条错误的标示
     * 
     * @param void
     * @return int
     */
    public function getErrorNo()
    {
        return $this->getConnector()->errno;
    }

    /**
     * 关闭或者销毁实例和链接
     * 
     * @param void
     * @return void
     */
    public function close()
    {
        $this->getConnector()->close();
        unset($this->_conn);
    }

    /**
     * 重载方法：执行 SQL
     * 
     * @param string $sql
     * @return mixed
     */
    public function query($sql)
    {
        $conn = $this->getConnector();
        $this->_statement = $conn->query($sql);
        if ($this->_statement !== FALSE)
        {
            $this->_relink = 0;
            return $this->_statement;
        }
        if (in_array($conn->errno, self::RELINK_LIST) && $this->_relink < self::MAX_RELINK)
        {
            $this->_relink++;
            $this->close();
            $this->getConnector();
            return $this->query($sql);
        }
        $this->onError(sprintf('QUERY FAILD:%s'. $sql));
    }
    
    /**
     * 执行写操作SQL
     *
     * @param string $sql SQL语句
     * @return int rows
     */
    public function exec($sql)
    {
        $mret = $this->query($sql);
        if ($mret)
        {
            return $this->getConnector()->affected_rows;
        }
        return 0;
    }

    /**
     * 获取最后一条查询讯息
     * 
     * @param void
     * @return int
     */
    public function lastInsertId()
    {
        return $this->getConnector()->insert_id;
    }

    /**
     * 返回调用当前查询后的结果集中的记录数
     * 
     * @param void
     * @return int
     */
    public function rowsCount()
    {
        if ($this->_statement === FALSE)
        {
            return 0;
        }
        if (is_bool($this->_statement))
        {
            return $this->getConnector()->affected_rows;
        }
        return $this->_statement->num_rows;
    }

    /**
     * 查询并获取 一条结果集
     * 
     * @param string $sql 查询的SQL语句
     * @return array || null
     */
    public function fetch($sql)
    {
        $mret = $this->query($sql);
        if ($mret === TRUE)
        {
            return [];
        }
        $row = $mret->fetch_array(MYSQLI_ASSOC);
        if ($row === NULL)
        {
            return [];
        }
        return $row;
    }
    
    /**
     * 查询并获取所有结果集
     * 
     * @param string $sql 查询的SQL语句
     * @return array || null
     */
    public function fetchAll($sql)
    {
        $mret = $this->query($sql);
        if ($mret === TRUE)
        {
            return  [];
        }
        $rows = $mret->fetch_all(MYSQLI_ASSOC);
        return $rows;
    }

    /**
     * 开始事务
     * 
     * @param void
     * @return bool
     */
    public function beginTransaction()
    {
        return $this->getConnector()->begin_transaction(TRUE);
    }

    /**
     * 提交事务
     * 
     * @param void
     * @return bool
     */
    public function commit()
    {
        return $this->getConnector()->commit();
    }

    /**
     * 事务回滚
     * 
     * @param void
     * @return bool
     */
    public function rollBack()
    {
        return $this->getConnector()->rollback();
    }

    /**
     * 析构函数 关闭连接
     *
     * @param void
     * @return void
     */
    public function __destruct()
    {
        $this->close();
    }
}
?>