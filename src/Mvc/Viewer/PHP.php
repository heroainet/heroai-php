<?php
/**
 * @Copyright (C), 2013-, King.
 * @Name PHP.php
 * @Author King
 * @Version Beta 1.0 
 * @Date 2017年3月12日下午3:37:03
 * @Desc
 * @Class List 
 * @Function List 
 * @History King 2013年3月12日下午3:37:03 0 第一次建立该文件
 *               King 2017年3月12日下午3:37:03 1 上午修改
 */
namespace Tiny\Mvc\Viewer;

/**
 * 原生的PHP解析引擎
 * 
 * @package Tiny\MVC\View\Engine
 * @since 2013-5-25上午08:22:54
 * @final 2017-3-12上午08:22:54
 */
class PHP extends Base
{

    /**
     * 获取输出的HTML内容
     * 
     * @param void
     * @return string
     */
    public function fetch($file, $isAbsolute = false)
    {
        if (! $isAbsolute)
        {
            $file = $this->_templateFolder . $file;
        }
        
        if (! is_file($file))
        {
            throw new ViewerException("viewer error: file $file is not a file");
        }
        
        ob_start();
        extract($this->_variables, EXTR_SKIP);
        
        include $file;
        
        $content = ob_get_contents();
        ob_clean();
        
        return $content;
    }
}
?>