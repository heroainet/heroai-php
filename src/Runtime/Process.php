<?php
/**
 * @Copyright (C), 2013-, King.
 * @Name Process.php
 * @Author King
 * @Version 1.0 
 * @Date: 2013-11-26上午06:50:38
 * @Description 对当前进程的一些操作和信息描述 
 * @Class List 
 * 1. Process
 * @Function 
 * @History <author> <time> <version > <desc> 
 king 2013-11-26上午06:50:38  1.0  第一次建立该文件
 */
namespace Tiny\Runtime;

/**
 * 对当前进程的一些操作和信息描述
 * 
 * @package Tiny
 * @since 2013-11-26上午06:51:37
 * @final 2013-11-26上午06:51:37
 */
class Process
{

    /**
     * 判断是否有加载某个扩展
     * 
     * @param string $ext 扩展名称
     * @return bool
     */
    public static function extensionLoaded($ext)
    {
        return extension_loaded($ext);
    }

    /**
     * 返回某个扩展里的所有函数
     * 
     * @param string $ext 扩展名称
     * @return array
     */
    public static function extensionFuncs($ext)
    {
        return get_extension_funcs(strtolower($ext));
    }

    /**
     * 返回已定义的所有变量数组 包括全局变量和用户自定义变量
     * 
     * @param void
     * @return array
     */
    public static function definedVars()
    {
        return get_defined_vars();
    }

    /**
     * 获取 PHP 脚本所有者的 GID
     * 
     * @param void
     * @return int
     */
    public static function gid()
    {
        return getmygid();
    }

    /**
     * 获取的当前进程ID
     * 
     * @param void
     * @return int
     */
    public static function pid()
    {
        return getmypid();
    }

    /**
     * 获取包含文件路径
     * 
     * @param void
     * @return string
     */
    public static function getIncludePath()
    {
        return get_include_path();
    }

    /**
     * 设置包含路径
     * 
     * @param string $path 新的包含路径
     * @return bool
     */
    public static function setIncludePath($path)
    {
        return set_include_path($path);
    }

    /**
     * 获取当前进程已经包含的脚本文件数组
     * 
     * @param void
     * @return array
     */
    public static function getIncludeFiles()
    {
        return get_included_files();
    }

    /**
     * 返回所有编译并加载模块名的 array
     * 
     * @param void
     * @return array
     */
    public static function loadedExtensions()
    {
        return get_loaded_extensions();
    }

    /**
     * 获取当前进程使用的峰值内存
     * 
     * @param void
     * @return int
     */
    public static function peakUsageMemory()
    {
        return memory_get_peak_usage();
    }

    /**
     * 设置进程的生命周期
     * 
     * @param int $num 为0 则不限制
     * @return void
     */
    public static function setLimit($num = 0)
    {
        return set_time_limit($num);
    }

    /**
     * 获取 PHP 脚本所有者的 UID
     * 
     * @param void
     * @return int
     */
    public static function uid()
    {
        return getmyuid();
    }

    /**
     * 获取当前进程使用的内存
     * 
     * @param void
     * @return int
     */
    public static function usageMemory()
    {
        return memory_get_usage();
    }

    /**
     * 调用系统数据的信息数组
     * 
     * @param void
     * @return array
     */
    public static function usage()
    {
        return getrusage();
    }
}
?>