<?php 
/**
 * @Copyright (C), 2013-, King.
 * @Name IFilter.php
 * @Author King
 * @Version Beta 1.0 
 * @Date 2017年3月9日下午9:18:52
 * @Desc
 * @Class List 
 * @Function List 
 * @History King 2017年3月9日下午9:18:52 0 第一次建立该文件
 *               King 2017年3月9日下午9:18:52 1 上午修改
 */
namespace Tiny\Filter;

use Tiny\Mvc\Request\Base as Request;
use Tiny\Mvc\Response\Base as Response;

/**
 * 过滤器接口
 * @package Tiny.Filter 
 * @since 
 * @final 
 */
interface IFilter
{
    public function doFilter(Request $req, Response $res);
}

?>