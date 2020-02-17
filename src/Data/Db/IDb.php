<?php 
/**
 * @Copyright (C), 2013-, King.
 * @Name IDb.php
 * @Author King
 * @Version Beta 1.0 
 * @Date 2017年4月4日下午12:12:36
 * @Desc
 * @Class List 
 * @Function List 
 * @History King 2017年4月4日下午12:12:36 0 第一次建立该文件
 *               King 2017年4月4日下午12:12:36 1 上午修改
 */
namespace Tiny\Data\Db;

use Tiny\Data\ISchema;

/**
 * 数据库驱动的 接口类
 *
 * @package Tiny.Data.Db
 * @since 2013-11-28上午06:50:11
 * @final 2013-11-28上午06:50:11
 */
interface IDb extends ISchema
{
    /**
     * 触发查询事件
     *
     * @param string $msg 查询内容
     * @param float $time
     * @return void
     */
    public function onQuery($msg, $time);

    /**
     * 错误发生事件
     *
     * @param void
     * @return void
     */
    public function onError($msg);

    /**
     * 获取最近一条错误的内容
     *
     * @param void
     * @return string
     */
    public function getErrorMSg();

    /**
     * 获取最近一条错误的标示
     *
     * @param
     *
     * @return
     *
     */
    public function getErrorNo();

    /**
     * 重载方法：执行 查询SQL
     *
     * @param string $sql
     */
    public function query($sql);

    /**
     * 关闭数据库链接
     *
     * @param void
     * @return void
     */
    public function close();
    
    /**
     * 执行写SQL
     *
     * @param string $sql SQL语句
     * @return int || FALSE
     */
    public function exec($sql);
    
    /**
     * 获取最后一条查询讯息
     *
     * @param void
     * @return int
     */
    public function lastInsertId();

    /**
     * 查询并获取 一条结果集
     * 
     * @param string $sql 查询的SQL语句
     * @return array || null
     */
    public function fetch($sql);

    /**
     * 查询并获取所有结果集
     *
     * @param string $sql 查询的SQL语句
     * @return array || null
     */
    public function fetchAll($sql);

    /**
     * 开始事务
     *
     * @param void
     * @return bool
     */
    public function beginTransaction();

    /**
     * 提交事务
     *
     * @param void
     * @return bool
     */
    public function commit();

    /**
     * 事务回滚
     *
     * @param void
     * @return bool
     */
    public function rollBack();
}
?>