<?php
/**
 * @Copyright (C), 2013-, King.
 * @Name Base.php
 * @Author King
 * @Version Beta 1.0 
 * @Date 2017年4月3日下午2:48:51
 * @Desc 模型基类
 * @Class List 
 * @Function List 
 * @History King 2017年4月3日下午2:48:51 0 第一次建立该文件
 *               King 2017年4月3日下午2:48:51 1 上午修改
 */
namespace Tiny\Mvc\Model;

use Tiny\Tiny;
/**
 * 模型基类
 * 
 * @package Tiny.Application.Model
 * @since 2017年4月3日下午2:49:43
 * @final 2017年4月3日下午2:49:43
 */
abstract class Base
{
    /**
     * 数据ID
     * @var string
     */
    protected $_dataId = 'default';
    
    /**
     * 构造函数
     *
     * @param
     * @return
     */    
    public function __construct()
    {
        
    }
    
    
    /**
     * 加载Model
     * 
     * @param string $modelName 模型名称
     * @return Db DbTable
     */
    public function getModel($modelName)
    {
        return $this->_app->getModel($modelName);
    }

    /**
     * 写入日志
     * 
     * @param string $id
     * @return bool
     */
    public function log($id, $message, $priority = 1, $extra = array())
    {
        return $this->log->log($id, $message, $priority, $extra);
    }


    
    /**
     * 魔法函数，加载视图层
     *
     * @param $key string 属性名
     * @return mixed view 视图层对象
     *         config 默认配置对象池
     *         cache 默认缓存对象池
     *         cookie Cookie操作对象
     *         session session操作对象
     *         lang 语言对象
     *         Model 尾缀为Model的模型对象
     */
    public function __get($key)
    {
        $ins = $this->_magicGet($key);
        if ($ins)
        {
            $this->{$key} = $ins;
        }
        return $ins;
    }
    
    /**
     * 魔术方式获取属性
     *
     * @param string $key
     * @return mixed
     */
    protected function _magicGet($key)
    {
        switch ($key)
        {
            case '_app' :
                return Tiny::getApplication();
            case 'data' :
                return $this->_app->getData();
            case 'config':
                return $this->_app->getConfig();
            case 'cache':
                return $this->_app->getCache();
            case ('Model' == substr($key, - 5)):
                return $this->_app->getModel(substr($key, 0, - 5));
            case 'log' :
                return $this->_app->getLogger();
            case 'lang':
                    return $this->_app->getLang();
            default:
                return false;
        }
    }    
}
?>