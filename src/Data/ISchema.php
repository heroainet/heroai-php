<?php
/**
 * @Copyright (C), 2013-, King.
 * @Name ISchema.php
 * @Author King
 * @Version Beta 1.0 
 * @Date 2017年4月4日上午12:38:08
 * @Desc
 * @Class List 
 * @Function List 
 * @History King 2017年4月4日上午12:38:08 0 第一次建立该文件
 *               King 2017年4月4日上午12:38:08 1 上午修改
 */
namespace Tiny\Data;

/**
 * Data结构的接口
 * 
 * @package Tiny.Data
 * @since 2013-11-28上午03:42:16
 * @final 2013-11-28上午03:42:16
 */
interface ISchema
{

    /**
     * 统一的构造函数
     * 
     * @param array $policy 默认为空函数
     * @return
     *
     */
    public function __construct(array $policy = array());

    /**
     * 返回连接后的类或者句柄
     * 
     * @param void
     * @return
     *
     */
    public function getConnector();
}
?>