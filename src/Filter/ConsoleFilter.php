<?php 
/**
 * @Copyright (C), 2013-, King.
 * @Name ConsoleFilter.php
 * @Author King
 * @Version Beta 1.0 
 * @Date 2017年3月9日下午9:21:05
 * @Desc
 * @Class List 
 * @Function List 
 * @History King 2017年3月9日下午9:21:05 0 第一次建立该文件
 *               King 2017年3月9日下午9:21:05 1 上午修改
 */
namespace Tiny\Filter;

use Tiny\Mvc\Request\Base as Request;
use Tiny\Mvc\Response\Base as Response;

/**
 * 命令行过滤器
 * @package 
 * @since 
 * @final 
 */
class ConsoleFilter implements IFilter
{
    /**
     * 开始过滤
     * {@inheritDoc}
     * @see \Tiny\Filter\IFilter::doFilter()
     */
    public function doFilter(Request $req, Response $res)
    {
        
    }
}
?>