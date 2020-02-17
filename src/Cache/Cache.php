<?php
/**
 *
 * @Copyright (C), 2011-, King.$i
 * @Name: Cache.php
 * @Author: King
 * @Version: Beta 1.0
 * @Date: Sat Dec 17 12:29:10 CST 2011
 * @Description:缓存主体类
 * @Class List:
 *  	1.Cache
 *  @Function List:
 *   1.
 *  @History:
 *      <author>    <time>                        <version >   <desc>
 *        King      Sat Dec 17 12:29:10 CST 2011  Beta 1.0           第一次建立该文件
 */
namespace Tiny\Cache;

/**
 * Cache
 * 
 * @package : Cache 缓存适配器
 * @since : Sat Dec 17 17:18:19 CST 2011
 * @final : Sat Dec 17 17:18:19 CST 2011
 */
class Cache implements ICache, \ArrayAccess
{

    /**
     * 适配器后端的类数组
     * 
     * @var array
     */
    protected static $_driverMap = array(
        'file' => 'Tiny\Cache\File' ,
        'memcached' => 'Tiny\Cache\Memcached' ,
        'redis' => 'Tiny\Cache\Redis'
    );

    /**
     * 单一实例
     * 
     * @var Cache
     */
    protected static $_instance;

    /**
     * 默认的缓存实例ID
     * 
     * @var string
     */
    protected $_defaultId = 'default';

    /**
     * 注册的缓存策略对象数组
     * 
     * @var array
     */
    protected $_policys = array();

    /**
     * 单一模式，获取实例
     * 
     * @param void
     * @return Cache
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
     * 注册缓存适配器的驱动类
     * 
     * @param string $type 缓存配置的类型名称
     * @param string
     * @return void
     */
    public static function regDriver($type, $className)
    {
        if (isset(self::$_driverMap[$type]))
        {
            throw new CacheException('Cache后端适配器注册失败：类型' . $type . '已存在');
        }
        self::$_driverMap[$type] = $className;
    }

    
    /**
     * 注册一个缓存策略
     * 
     * @param array $prolicy 策略数组
     * @return void
     *
     */
    public function regPolicy(array $policy)
    {
        $id = $policy['id'];
        $driver = $policy['driver'];
        if (! $id)
        {
            throw new CacheException('Cache策略添加失败：policy需要设置ID作为缓存实例标示');
        }
        if (! isset(self::$_driverMap[$driver]))
        {
            throw new CacheException('Cache策略添加失败：driver不存在或者没有设置');
        }
        if ($this->_policys[$id])
        {
            throw new CacheException('Cache策略添加失败：ID:' . $id . '已存在 ');
        }
        $policy['className'] = self::$_driverMap[$driver];
        return $this->_policys[$id] = $policy;
    }

    /**
     * 设置默认的缓存ID
     * 
     * @param string $id 缓存ID
     * @return void
     */
    public function setDefaultId($id)
    {
        $id = (string)$id;
        if (! $this->_policys[$id])
        {
            throw new CacheException('设置默认缓存ID失败:ID:' . $id . '不存在');
        }
        $this->_defaultId = $id;
    }

    /**
     * 获取默认的缓存ID
     * 
     * @param void
     * @return string
     */
    public function getDefaultId()
    {
        return $this->_defaultId;
    }

    /**
     * 根据缓存策略的身份标识获取一个缓存句柄
     * 
     * @param : $id CachePolicy 的 id；
     * @return : ICache
     */
    public function getCache($id = null)
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
        
        $policy = & $this->_policys[$id];
        if (! isset($policy))
        {
            throw new CacheException('获取ID为' . $id . '的缓存实例失败：该缓存策略ID $id ' . $id . '没有配置profile.cache.prolicy或者注册!');
        }
        if ($policy['instance'])
        {
            return $policy['instance'];
        }
        $policy['instance'] = new $policy['className']($policy);
        return $policy['instance'];
    }

    /**
     * 根据ID设置缓存实例
     * 
     * @param string $id 缓存ID
     * @param ICache 实现了缓存接口的缓存实例
     * @return void
     */
    public function setCache($id, ICache $cache)
    {
        if ($this->getCache($id))
        {
            throw new CacheException('设置缓存实例失败:ID' . $id . '已存在');
        }
        $this->_policys[$id] = array('instance' => $cache);
    }

    /**
     * 通过默认的缓存实例设置缓存
     * 
     * @param string $key 缓存的键 $key为array时 可以批量设置缓存
     * @param mixed $value 缓存的值 $key为array时 为设置生命周期的值
     * @param int $life 缓存的生命周期
     * @return bool
     */
    public function set($key, $value = null, $life = null)
    {
        return $this->_getDefCache()->set($key, $value, $life);
    }

    /**
     * 获取缓存
     * 
     * @param string || array $key 获取缓存的键名 如果$key为数组 则可以批量获取缓存
     * @return mixed
     */
    public function get($key)
    {
        return $this->_getDefCache()->get($key);
    }

    /**
     * 通过默认的缓存实例移除缓存
     * 
     * @param string $key 缓存的键 $key为array时 可以批量删除
     * @return bool
     */
    public function remove($key)
    {
        return $this->_getDefCache()->remove($key);
    }

    /**
     * 通过默认的缓存实例判断缓存是否存在
     * 
     * @param string $key 键
     * @return bool
     */
    public function exists($key)
    {
        return $this->_getDefCache()->exists($key);
    }

    /**
     * 清除默认缓存实例的所有缓存
     * 
     * @param : void
     * @return : void
     */
    public function clean()
    {
        return $this->_getDefCache()->clean();
    }

    /**
     * 数组接口之设置
     * 
     * @param $key string 键
     * @param $value ICache 实例
     * @return void
     */
    public function offsetSet($key, $cache)
    {
        if (null == $key)
        {
            throw new CacheException('缓存实例key不允许为空!');
        }
        if (! $cache instanceof ICache)
        {
            throw new CacheException('缓存实例没有实现ICache接口');
        }
        $this->_policys[$key] = $cache;
    }

    /**
     * 数组接口之获取缓存实例
     * 
     * @param $key string 键
     * @return ICache
     */
    public function offsetGet($id)
    {
        return $this->getCache($id);
    }

    /**
     * 数组接口之是否存在该值
     * 
     * @param $key string 键
     * @return boolean
     */
    public function offsetExists($id)
    {
        return $this->getCache($id) ? true : false;
    }

    /**
     * 数组接口之移除该值
     * 
     * @param $key string 键值
     * @return void
     */
    public function offsetUnset($id)
    {
        unset($this->_policys[$id]);
    }

    /**
     * 获取默认的缓存实例
     * 
     * @param void
     * @return ICache
     */
    protected function _getDefCache()
    {
        return $this->getCache();
    }

    /**
     * 构造函数 只能通过Cache::getInstance()获取新实例
     * 
     * @param void
     * @return void
     */
    protected function __construct()
    {
        
    }
}
?>