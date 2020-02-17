    <?php
/**
 *
 * @Copyright (C), 2011-, King.$i
 * @Name  Db.php
 * @Author  King
 * @Version  Beta 1.0
 * @Date  Sat Jan 07 23:43:54 CST 2012
 * @Description 数据库操作类
 * @Class List
 *  	1.Db 数据库操作类
 *  @Function List
 *   1.
 *  @History
 *      <author>    <time>                        <version >   <desc>
 *        King      Sat Jan 07 23:43:54 CST 2012  Beta 1.0           第一次建立该文件
 *
 */
namespace Tiny\Mvc\Model;

use Tiny\Data\Db\Schema;

/**
 * 数据库的表操作模型
 * 
 * @author King
 * @package Model
 * @since 2013-3-31下午02:10:22
 * @final 2013-3-31下午02:10:22}
 */
abstract class Table extends Base
{

    /**
     * 操作的表名
     * 
     * @var string
     */
    protected $_tableName = null;

    /**
     * 默认的写数据库实例Id
     * 
     * @var string
     */
    protected $_writeId = 'default';

    /**
     * 默认的读数据库实例ID
     * 
     * @var string
     */
    protected $_readId = null;

    /**
     * Data写实例
     * 
     * @var Schema
     */
    protected $_writeSchema;

    /**
     * Data读实例
     * 
     * @var Schema
     */
    protected $_readSchema;

    /**
     * 构造函数 初始化获取的数据库连接实例id
     * 
     * @param string $tableName 操作的表名称
     * @param string $writeId 写数据库的Data ID
     * @param string || array $readId 读数据库的Data ID
     * @return void
     */
    public function __construct($tableName = null, $writeId = 'default', $readId = null)
    {
        $this->_writeId = $writeId;
        $this->_readId = $readId;
        if (! $tableName)
        {
            throw new ModelException('Model.Table实例化失败，必须设置tablename');
        }
        $this->_tableName = (string) $tableName;
    }

    /**
     * 执行任何SQL语句 query别名
     * 
     * @param string $sql
     * @return bool
     */
    public function execute($sql)
    {
        return $this->getWriteSchema()->query($sql);
    }

    /**
     * 执行任何SQL语句
     * 
     * @param string $sql
     * @return bool
     */
    public function query($sql)
    {
        return $this->getWriteSchema()->query($sql);
    }

    /**
     * 返回处理后的查询二维结果集,返回的结果格式为:
     * 如果SQL的结果集为:
     * -uid- -name- -age- (字段名)
     * u1 yuan 20 (第一行记录)
     * u2 zhan 19 (第二行记录)
     * 则则函数返回的数组值为:
     * array('u1'=>array('uid'=>'u1','name'=>'yuan','age'=>20),
     * 'u2'=>array('uid'=>'u2','name'=>'zhan','age'=>19)
     * )
     * 
     * @param string $sql SQL查询语句
     * @return array
     */
    public function fetchAssoc($sql)
    {
        return $this->getReadSchema()->fetchAssoc($sql);
    }

    /**
     * 执行SQL并返回所有结果集
     * 
     * @param string $sql SQL查询语句
     * @return array
     */
    public function fetchAll($sql)
    {
        return $this->getReadSchema()->fetchAll($sql);
    }

    /**
     * 执行SQL并返回结果集的第一行(一维数组)
     * 
     * @param string $sql SQL查询语句
     * @return array
     */
    public function fetch($sql)
    {
        return $this->getReadSchema()->fetch($sql);
    }

    /**
     * 返回结果集中第一列的所有值(一维数组)
     * 
     * @param string $sql SQL查询语句
     * @return array
     */
    public function fetchColumn($sql)
    {
        return $this->getReadSchema()->fetchColumn($sql);
    }

    /**
     * 执行SQL并返回结果集中第一行第一列的值
     * 
     * @param string $sql SQL查询语句
     * @return array
     */
    public function fetchOneColumn($sql)
    {
        return $this->getReadSchema()->fetchOneColumn($sql);
    }

    /**
     * 执行SQL并返回结果集中第一个单元格的值
     * 
     * @param string $sql SQL查询语句
     * @return array
     */
    public function fetchCell($sql)
    {
        return $this->getReadSchema()->fetchOneColumn($sql);
    }

    /**
     * 返回最后执行 Insert() 操作时表中有 auto_increment 类型主键的值
     * 
     * @param void
     * @return int
     */
    public function getLastInsertId()
    {
        return $this->getWriteSchema()->getLastInsertId();
    }

    /**
     * 最后 DELETE UPDATE 语句所影响的行数
     * 
     * @param void
     * @return int
     */
    public function getAffectedRows()
    {
        return $this->getWriteSchema()->getAffectedRows();
    }

    /**
     * 返回调用当前查询后的结果集中的记录数
     * 
     * @param void
     * @return int
     */
    public function getRowCount()
    {
        return $this->getReadSchema()->getRowCount();
    }

    /**
     * 组合各种条件查询并返回结果集
     * 说明:$where 可以是字符串或数组,如果定义为数组则格式有如下两种:
     * $where = array('id'=>1,
     * 'name'=>'yuanwei');
     * 解析后条件为: "id=1 AND name='yuanwei'"
     * $where = array('id'=>array('>='=>1),
     * 'name'=>array('like'=>'%yuanwei%'));
     * 解析后条件为: "id>=1 AND name LIKE '%yuanwei%'"
     * 注意:#where 中的条件解析后都是用 AND 连接条件,其它形式请直接用字符串的方法传值
     * 
     * @param string || array $fields 字段名 'uid,name' || array('uid','name') 支持别名方式
     * @param mixed $where 条件
     * @param string $order 排序字段
     * @param string $limit 返回记录行,格式 "0,10"
     * @param string $group 分组字段
     * @param string $having 筛选条件
     * @return array
     */
    public function select($fields = '*', $where = '', $order = '', $limit = '', $group = '', $having = '')
    {
        return $this->getReadSchema()->select($fields, $this->_tableName, $where, $order, $limit, $group, $having);
    }

    /**
     * 从查询条件开始建立链式查询
     * 
     * @param string $where 查询条件
     * @return Schema
     */
    public function find($where = '')
    {
        return $this->getReadSchema()->from($this->_tableName)->where($where);
    }

    /**
     * 更新记录,执行 UPDATE 操作
     * 说明: $arrSets 格式如下:
     * $arrSets = array('uid'=>1,
     * 'name'=>'yuanwei');
     * 解析后SET为: "uid=1,name='yuanwei'"
     * 
     * @param array $arrSets 设置的字段值
     * @param mixed $where 条件,详细请看 Select()成员
     * @param string $order 排序字段
     * @param int $limit 记录行
     * @param string $group 分组字段
     * @return bool
     */
    public function update($arrSets, $where = '', $order = '', $limit = '', $group = '')
    {
        return $this->getWriteSchema()->update($this->_tableName, $arrSets, $where, $order, $limit, $group);
    }

    /**
     * 插入记录,执行 INSERT 操作
     * 说明:有关 $arrSets 数组的定义请看: Update()成员
     * 
     * @param array $arrSets 插入的字段
     * @param boolean $replace 是否采用 REPLACE INTO 的方式插入记录
     * @return int
     */
    public function insert($arrSets, $replaceInto = false)
    {
        return $this->getWriteSchema()->insert($this->_tableName, $arrSets, $replaceInto);
    }

    /**
     * 删除记录,执行 DELETE 操作,返回删除的记录行数
     * 
     * @param mixed $where 条件,详细请看 Select()成员
     * @param string $order 排序字段
     * @param string $limit 记录行
     * @param string $group 分组
     */
    public function delete($where, $order = '', $limit = '', $group = '')
    {
        return $this->getWriteSchema()->delete($this->_tableName, $where, $order, $limit, $group);
    }

    /**
     * 求记录数
     * 说明:如果是求表的所有记录(没有WHERE),对于MyISAM表 $countField 请用 '*',否则请指定字段名
     * 
     * @param mixed $where 条件
     * @param string $countField COUNT字段名
     * @param string $group 分组
     * @return int
     */
    public function count($where = '', $countField = 'COUNT(*)', $group = '')
    {
        return $this->getReadSchema()->count($this->_tableName, $where, $countField, $group);
    }

    /**
     * 开始事务
     * 
     * @param void
     * @return bool
     */
    public function autocommit()
    {
        return $this->getWriteSchema()->autocommit();
    }

    /**
     * 提交事务
     * 
     * @param void
     * @return bool
     */
    public function commit()
    {
        return $this->getWriteSchema()->commit();
    }

    /**
     * 事务回滚
     * 
     * @param void
     * @return bool
     */
    public function rollBack()
    {
        return $this->getReadSchema()->rollBack();
    }

    /**
     * 返回MYSQL系统中当前所有可用的数据库
     * 
     * @param
     *
     * @return array
     */
    public function getDbs()
    {
        return $this->getReadSchema()->getDbs();
    }

    /**
     * 返回数据库中所有的表,如果为空则返回当前数据库中所有的表名
     * 
     * @param void
     * @return array
     */
    public function getTables()
    {
        return $this->getReadSchema()->getTables();
    }

    /**
     * 删除整个表
     * 
     * @param void
     * @return bool
     */
    public function deleteTable()
    {
        return $this->getWriteSchema()->deleteTable($this->_tableName);
    }

    /**
     * 清空表,执行 TRUNCATE TABLE 操作
     * 
     * @param void
     * @return bool
     */
    public function clear()
    {
        return $this->getWriteSchema()->clearTable($this->_tableName);
    }

    /**
     * 返回指定表的所有字段名
     * 
     * @param void
     * @return array
     */
    public function getColumns()
    {
        return $this->getReadSchema()->getTableColumns($this->_tableName);
    }

    /**
     * 返回指定表的所有字段名
     * 
     * @param void
     * @return bool
     */
    public function getAllColumnNames()
    {
        return $this->getReadSchema()->getAllColumns($this->_tableName);
    }

    /**
     * 优化表,执行 OPTIMIZE TABLE 操作
     * 
     * @param void
     * @return bool
     */
    public function optimize()
    {
        return $this->getWriteSchema()->optimize($this->_tableName);
    }

    /**
     * 修复表,执行 REPAIR TABLE 操作
     * 
     * @param void
     * @return bool
     */
    public function repair()
    {
        return $this->getWriteSchema()->repair($this->_tableName);
    }

    /**
     * 格式化用于数据库的字符串
     * 
     * @param string $str
     * @return string
     */
    public function escape($str)
    {
        return $this->getWriteSchema()->escape($str);
    }

    /**
     * 获取读的DB实例
     * 
     * @param void
     * @return Schema
     */
    public function getReadSchema()
    {
        if ($this->_readSchema)
        {
            return $this->_readSchema;
        }
        // read如果没有设置 直接返回写入的writekey
        if (!$this->_readId)
        {
            $this->_readSchema = $this->getWriteSchema();
        }
        elseif (is_array($this->_readId))
        {
            $readIndex = rand(0, count($this->_readId) - 1);
            $this->_readSchema = $this->_getSchema($this->_readId[$readIndex]);
        }
        else
        {
            $this->_readSchema = $this->_getSchema($this->_readId);
        }
        return $this->_readSchema;
    }

    /**
     * 获取读的Db Schema实例
     * 
     * @param void
     * @return Schema
     */
    public function getWriteSchema()
    {
        if (!$this->_writeSchema)
        {
            $this->_writeSchema = $this->_getSchema($this->_writeId);
        }
        return $this->_writeSchema;
    }

    /**
     * 获取数据库操作实例
     * 
     * @param string $id
     * @return Schema
     */
    protected function _getSchema($id)
    {
        $schema = $this->data->getData($id);
        if (! $schema instanceof Schema)
        {
            throw new ModelException("Model.Db获取失败:ID:{$id}并非 继承成自 Tiny\Data\Db\Schema实例!");
        }
        return $schema;
    }
}
?>