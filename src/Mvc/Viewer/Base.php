<?php
/**
 * @Copyright (C), 2013-, King.
 * @Name Base.php
 * @Author King
 * @Version Beta 1.0 
 * @Date 2017年3月13日上午12:16:34
 * @Desc
 * @Class List 
 * @Function List 
 * @History King 2017年3月13日上午12:16:34 0 第一次建立该文件
 *               King 2017年3月13日上午12:16:34 1 上午修改
 */
namespace Tiny\Mvc\Viewer;

/**
 * 视图基类
 * 
 * @package Tiny.Application.Viewer
 * @since 2017年3月12日下午3:29:18
 * @final 2017年3月12日下午3:29:18
 */
abstract class Base implements IViewer
{

    /**
     * 模板目录
     * 
     * @var string
     */
    protected $_templateFolder;

    /**
     * 模板解析目录
     * 
     * @var string
     */
    protected $_compileFolder;

    /**
     * 预先分配变量
     * 
     * @var array
     */
    protected $_variables = array();

    /**
     * 设置模板引擎的模板文件夹
     * 
     * @param $path string 文件夹路径
     * @return void
     */
    public function setTemplateFolder($path)
    {
        $this->_templateFolder = $path;
    }

    /**
     * 获取模板引擎的模板文件夹
     * 
     * @param $path string 文件夹路径
     * @return string 视图文件夹路径
     */
    public function getTemplateFolder()
    {
        return $this->_templateFolder;
    }

    /**
     * 设置模板引擎的编译文件夹
     * 
     * @param $path string 文件夹路径
     * @return void
     */
    public function setCompileFolder($path)
    {
        $this->_compileFolder = $path;
    }

    /**
     * 获取模板引擎的编译文件夹
     * 
     * @param $path string 文件夹路径
     * @return string 编译存放路径
     */
    public function getCompileFolder()
    {
        return $this->_compileFolder;
    }

    /**
     * 分配变量
     * 
     * @param $key string 变量分配的键
     * @param $value mixed 分配的值
     * @return void
     *
     */
    public function assign($key, $value = null)
    {
        if (is_array($key))
        {
            $this->_variables = array_merge($this->_variables, $key);
            return;
        }
        $this->assign($key, $value);
    }

    /**
     * 输出解析内容
     * 
     * @param $file string 模板路径
     * @return void
     *
     */
    public function display($file, $isAbsolute = false)
    {
        echo $this->fetch($file, $isAbsolute);
    }
}
?>