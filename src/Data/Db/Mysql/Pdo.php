<?php
/**
 *
 * @copyright (C), 2013-, King.
 * @name Pdo.php
 * @author King
 * @version 1.0
 *          @Date: 2013-11-28上午06:55:47
 *          @Description
 *          @Class List
 *          @Function
 *          @History <author> <time> <version > <desc>
 *          king 2013-11-28上午06:55:47 1.0 第一次建立该文件
 */
namespace Tiny\Data\Db\Mysql;

use Tiny\Data\Db\IDb;
use Tiny\Data\Data;

/**
 * mysqld的PDO构造方式
 * 
 * @package Tiny.Data.Db.Mysql
 * @since 2013-11-28上午06:56:26
 * @final 2013-11-28上午06:56:26
 */
class Pdo implements IDb
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
    const RELINK_LIST = [2006 ,2013];

    /**
     * 配置数组
     * 
     * @var array
     * @access protected
     */
    protected $_conf;

    /**
     * 连接标示
     * 
     * @var PDO
     * @access protected
     */
    protected $_conn;

    /**
     * 链接返回的PDOStatement
     * 
     * @var mixed PDOStatement
     */
    protected $_statement;

    /**
     * 自动重连次数
     * 
     * @var int
     */
    protected $_relink = 0;

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
     * @param float $time 用时时长
     * @return void
     */
    public function onQuery($msg, $time)
    {
	   Data::addQuery($msg, $time, __CLASS__);
    }

    /**
     * 错误发生
     * 
     * @param void
     * @return void
     */
    public function onError($msg)
    {
        $info = $this->getConnector()->errorInfo();
        throw new MysqlException(sprintf("%s ErrorNO:%d,%s", $msg, $info[0], $info[2]));
    }

    /**
     * 获取最近一条错误的内容
     * 
     * @param void
     * @return string
     */
    public function getErrorMSg()
    {
        return $this->getConnector()->errorInfo()[2];
    }

    /**
     * 获取最近一条错误的标示
     * 
     * @param void
     * @return int
     *
     */
    public function getErrorNo()
    {
        return $this->getConnector()->errorCode()[1];
    }

    /**
     * 获取连接
     * 
     * @param void
     * @return bool
     */
    public function getConnector()
    {
        if ($this->_conn)
        {
            return $this->_conn;
        }
       
        $conf = $this->_conf;
        $opt = [];
        $opt[\PDO::ATTR_CASE] = \PDO::CASE_LOWER;
        $opt[\PDO::ATTR_ERRMODE] = \PDO::ERRMODE_WARNING;
        $opt[\PDO::ATTR_TIMEOUT] = $conf['timeout'];        
        if ($this->_policy['pconnect'])
        {
            $opt[\PDO::ATTR_PERSISTENT] = TRUE;
        }
        try
        {
            $interval = microtime(TRUE);
            $dns = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $conf['host'], $conf['port'], $conf['dbname'],$conf['charset']);
            $this->_conn = new \PDO($dns, $conf['user'], $conf['password'], $opt);
            $this->onQuery(sprintf('连接...%s@%s:%s', $conf['user'], $conf['host'], $conf['port']), microtime(TRUE) - $interval);        
        }
        catch (\PDOException $e)
        {
            throw new MysqlException($e->getMessage());
        }
        return $this->_conn;
    }

    /**
     * 重载方法：执行 SQL
     * 
     * @param string $sql
     * @return mixed PDOstatement || FALSE
     */
    public function query($sql)
    {
        $interval = microtime(true);
        $conn = $this->getConnector();
        $this->_statement = $conn->query($sql);
        $this->onQuery($sql, microtime(true) - $interval);
        if ($this->_statement)
        {
            $this->_relink = 0;
            return $this->_statement;
        }
        $errNo = $conn->errorInfo()[1];
        if (in_array($errNo, self::RELINK_LIST) && $this->_relink < self::RELINK_MAX)
        {
            $this->_relink++;
            unset($this->_conn);
            $this->getConnector();
            return $this->query($sql);
             
        }
        $this->onError(sprintf('QUERY FAILD:%s'. $sql));
        return FALSE;
    }
    
    /**
     * 执行写SQL
     *
     * @param string $sql SQL语句
     * @return int || FALSE
     */
    public function exec($sql)
    {
        $interval = microtime(true);
        $conn = $this->getConnector();
        $ret = $conn->query($sql);
        $this->onQuery($sql, microtime(true) - $interval);
        if ($ret !== FALSE)
        { 
            $this->_relink = 0;
            return $ret;
        }
        
        $errNo = $conn->errorInfo()[1];
        if (in_array($errNo, self::RELINK_LIST) && $this->_relink < self::RELINK_MAX)
        {
            $this->_relink++;
            $this->close();
            $this->getConnector();
            return $this->exec($sql);
             
        }
        
        $this->onError(sprintf('EXEC FAILD:%s'. $sql));
        return FALSE;
    }

    /**
     * 获取插入语句的最后一个ID 必须有主键ID
     * 
     * @param void
     * @return int
     */
    public function lastInsertId()
    {
        return $this->getConnector()->lastInsertId();
    }

    /**
     * 返回调用当前查询后的结果集中的记录数
     * 
     * @param void
     * @return int
     */
    public function rowCount()
    {
        if (!$this->_statement)
        {
            return 0;
        }
        return  $this->_statement->rowCount();
    }

    /**
     * 查询并获取 一条结果集
     * 
     * @param string $sql 查询的SQL语句
     * @return array || null
     */
    public function fetch($sql)
    {
        $statement = $this->query($sql);
        if ($statement === FALSE)
        {
            return [];
        }
        return $statement->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * 查询并获取所有结果集
     * 
     * @param string $sql 查询的SQL语句
     * @return array || null
     */
    public function fetchAll($sql)
    {
        $statement = $this->query($sql);
        if ($statement === FALSE)
        {
            return [];
        }
        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * 关闭或者销毁实例和链接
     *
     * @param void
     * @return void
     */
    public function close()
    {
        unset($this->_conn);
    }
    
    /**
     * 开始事务
     * 
     * @param void
     * @return bool
     */
    public function beginTransaction()
    {
        return $this->getConnector()->beginTransaction();
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
}
?>
