<?php 
/**
 * @Copyright (C), 2013-, King.
 * @Name WebFilter.php
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
 * Web过滤器
 * @package 
 * @since 
 * @final 
 */
class WebFilter implements IFilter
{
    /**
     * 开始过滤
     * {@inheritDoc}
     * @see \Tiny\Filter\IFilter::doFilter()
     */
    public function doFilter(Request $req, Response $res)
    {
        
    }
    
    /**
     * 去除XSS注入
     * @param array $data
     * @return array
     */
    public function filterXSS($data)
    {
        return htmlspecialchars($data);
    }
}
?>