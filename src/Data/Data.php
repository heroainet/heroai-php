<?php
/**
 *
 * @Copyright (C), 2011-, King.$i
 * @Name Data.php
 * @Author King
 * @Version Beta 1.0
 * @Date Sun Dec 25 23:35:04 CST 2011
 * @Description 数据代理层的命名空间
 * @Class List:
 *  	1.Data 数据库模型类
 *  @Function List:
 *   1.
 *  @History
 *      <author>    <time>                        <version >   <desc>
 *        King      Sun Dec 25 23:35:04 CST 2011  Beta 1.0           第一次建立该文件
 *
 */
namespace Tiny\Data;

/**
 * 数据库模型操作类
 * 
 * @package Data
 * @since ：Wed Dec 28 09:09:58 CST 2011
 * @final :Wed Dec 28 09:09:58 CST 2011
 */
class Data implements \ArrayAccess
{
    /**
     * 最多保存纪录DB的QUERY数目
     * @var integer
     */
    const DB_QUERY_SAVE_MAX = 100;
    
    /**
     * 实例
     * 
     * @var Data
     */
    protected static $_instance;

    /**
     * 已经注册的网络连接器
     * 
     * @var array
     */
    protected static $_driverMap = array(
        'db' => 'Tiny\Data\Db\Schema',
        'memcached' => 'Tiny\Data\Memcached\Schema',
        'redis' => 'Tiny\Data\Redis\Schema',
        'ssdb' => 'Tiny\Data\Ssdb\Schema'
    )
    ;

    /**
     * 执行的数据库语句集合
     * void
     * 
     * @var void
     */
    protected static $_querys = [];

    /**
     * Data策略数组
     * 
     * @var array
     */
    private $_policys = [];

    /**
     * 默认使用的Data ID
     * 
     * @var string
     */
    private $_defaultId = 'default';

    /**
     * : 已经实例化的Data实例
     * 
     * @var : array
     */
    private $_datas = array();

    /**
     * 添加语句执行信息
     * 
     * @param string $sql sql语句
     * @param int $time 执行时间
     * @param string $engineName 数据库引擎
     * @return void
     */
    public static function addQuery($sql, $time, $engineName)
    {
        $qlen = count(self::$_querys);
        if (self::DB_QUERY_SAVE_MAX < $qlen)
        {
            array_shift(self::$_querys);
        }
        self::$_querys[] = array(
            'sql' => $sql,
            'time' => $time,
            'engine' => $engineName
        );
    }

    /**
     * 获取查询集合
     * 
     * @param void
     * @return array
     */
    public static function getQuerys()
    {
        return self::$_querys;
    }

    /**
     * : 单一模式，获取Data实例
     * 
     * @param : void
     * @return : Data
     */
    public static function getInstance()
    {
        if (!self::$_instance)
        {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * 注册数据驱动
     * 
     * @param void
     * @return void
     */
    public static function regDriver($id, $className)
    {
        if (self::$_driverMap[$id])
        {
            throw new DataException('添加数据驱动失败:ID' . $id . '已经存在');
        }
        self::$_driverMap[$id] = $className;
    }

    /**
     * :增加一个数据策略
     * 
     * @param :$prolicy CachePolicy
     * @return :void
     */
    public function addPolicy(array $policy)
    {
        $id = $policy['id'];
        if (strpos($policy['driver'], '.') > - 1)
        {
            $ds = explode('.', $policy['driver']);
            $policy['driver'] = $ds[0];
            $policy['schema'] = $ds[1];
        }
        $className = self::$_driverMap[$policy['driver']];
        if (! isset($id))
        {
            throw new DataException('添加数据策略错误:Data.id没有设置');
        }
        if (! $className)
        {
            throw new DataException('添加数据策略错误:Data.driver' . $policy['driver'] . '没有注册');
        }
        $policy['className'] = $className;
        $this->_policys[$id] = $policy;
    }

    /**
     * : 根据数据策略的ID标识获取一个数据句柄
     * 
     * @param : $id Data 的 id；
     * @return : ISchema
     */
    public function getData($id = null)
    {
        if ($id == null)
        {
            $id = $this->_defaultId;
            if (! $this->_policys[$id])
            {
                $p = current($this->_policys);
                $id = $p['id'];
                $this->_defaultId = $id;
            }
        }
        
        if ($this->_datas[$id])
        {
            return $this->_datas[$id];
        }
        $policy = $this->_policys[$id];
        if (! $policy)
        {
            throw new DataException('获取Data实例错误:该数据策略ID:' . $id . '不存在!');
        }
        $className = $policy['className'];
        unset($policy['id'], $policy['className']);
        $data = new $className($policy);
        if (! $data instanceof ISchema)
        {
            throw new DataException('获取Data实例错误:' . $className . '没有实现接口Tiny\Data\ISchema!');
        }
        $this->_datas[$id] = $data;
        return $data;
    }

    /**
     * : 数组接口之设置
     * 
     * @param : $id data标示
     * @param : $value ISchema实例
     * @return :
     */
    public function offsetSet($id, $data)
    {
        if (null == $id)
        {
            throw new DataException('赋值$id不允许为空!', E_ERROR);
        }
        if (! $data instanceof ISchema)
        {
            throw new DataException('赋值对象需要为数据接口的实例', E_ERROR);
        }
        $this->_datas[$id] = $data;
    }

    /**
     * : 数组接口之获取数据实例
     * 
     * @param : $key 键
     * @return : ICache
     */
    public function offsetGet($id)
    {
        return $this->getData($id);
    }

    /**
     * : 数组接口之是否存在该值
     * 
     * @param : $id 键
     * @return : boolean
     */
    public function offsetExists($id)
    {
        return $this->getData($id) ? true : false;
    }

    /**
     * : 数组接口之移除该值
     * 
     * @param : $id 键
     * @return : void
     */
    public function offsetUnset($id)
    {
        unset($this->_datas[$id]);
        unset($this->_prolicys[$id]);
    }

    /**
     * 通过id来改变当前的默认数据操作实例
     * 
     * @param string $id 数据操作实例ID
     * @return void
     */
    public function setDefaultId($id)
    {
        if (! isset($this->_policys[$id]))
        {
            throw new DataException('设置默认的数据策略ID失败:ID不存在');
        }
        $this->_defaultId = (string) $id;
    }

    /**
     * 获取默认获取的数据代理ID
     * 
     * @param void
     * @return void
     */
    public function getDefaultId()
    {
        return $this->_defaultId;
    }

    /**
     * 限制构造函数只能自身创建实例，以满足单例模式的强制约束
     * 
     * @param void
     * @return void
     */
    protected function __construct()
    {
        
    }

    /**
     * 代理默认的数据库连接
     * 
     * @param $method string 函数名
     * @param 参数数组
     * @return mixed
     */
    public function __call($method, $args)
    {
        return call_user_func_array(array($this->getData(), $method), $args);
    }
}
?>