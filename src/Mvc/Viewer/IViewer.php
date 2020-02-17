<?php 
/**
 * @Copyright (C), 2013-, King.
 * @Name IViewer.php
 * @Author King
 * @Version Beta 1.0 
 * @Date 2017年3月13日上午12:17:34
 * @Desc
 * @Class List 
 * @Function List 
 * @History King 2017年3月13日上午12:17:34 0 第一次建立该文件
 *               King 2017年3月13日上午12:17:34 1 上午修改
 */
namespace Tiny\Mvc\Viewer;

/**
 * 视图模板引擎接口
 *
 * @package Tiny.Application.Viewer
 * @since : Mon Dec 12 01:06 15 CST 2011
 * @final : Mon Dec 12 01:06 15 CST 2011
 */
interface IViewer
{

    /**
     * 设置模板变量
     *
     * @param $key string 键名 为Array时可设置多个参数名
     * @param $value string 值
     * @return bool
     */
    public function assign($key, $value = null);

    /**
     * 输出模板解析后的数据
     *
     * @param string $file 文件路径
     * @param bool $isAbsolute 是否为绝对路径
     * @return string
     */
    public function fetch($file, $isAbsolute = false);

    /**
     * 显示解析后的视图内容
     *
     * @param string $file 视图文件路径
     * @param string $isAbsolute 是否为绝对路径
     * @return string
     */
    public function display($file, $isAbsolute = false);
}
?>