<?php
/**
 * @Copyright (C), 2013-, King.
 * @Name Gc.php
 * @Author King
 * @Version 1.0 
 * @Date: 2013-11-26上午06:47:05
 * @Description 垃圾回收控制类
 * @Class List 
 * @Function 
 * @History <author> <time> <version > <desc> 
 king 2013-11-26上午06:47:05  1.0  第一次建立该文件
 */
namespace Tiny\Runtime;

/**
 * 垃圾回收控制类
 * 
 * @package Tiny
 * @since 2013-11-26上午06:47:31
 * @final 2013-11-26上午06:47:31
 */
final class Gc
{

    /**
     * 强制收集所有现存的垃圾循环周期。
     * 
     * @param void
     * @return int
     */
    public static function collect()
    {
        return gc_collect_cycles();
    }

    /**
     * 激活循环引用收集器
     * 
     * @param void
     * @return void
     */
    public static function enable()
    {
        return gc_enable();
    }

    /**
     * 是否有开启垃圾循环引用收集器
     * 
     * @param void
     * @return bool
     */
    public static function isEnable()
    {
        return gc_enabled();
    }

    /**
     * 停用循环引用收集器。
     * 
     * @param void
     * @return void
     */
    public static function disable()
    {
        return gc_disable();
    }
}
?>