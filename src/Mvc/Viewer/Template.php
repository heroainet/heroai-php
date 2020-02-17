<?php
/**
 * @Copyright (C), 2011-, King.
 * @Name: Template.php
 * @Author: King
 * @Version: Beta 1.0
 * @Date: 2013-5-25上午08:21:54
 * @Description:
 * @Class List:
 *  	1.
 *  @Function List:
 *   1.
 *  @History:
 *      <author>    <time>                        <version >   <desc>
 *        King  2013-5-25上午08:21:54     Beta 1.0           第一次建立该文件
 *
 */
namespace Tiny\Mvc\Viewer;

use Tiny\Mvc\Viewer\Helper\Url;
/**
 * 简单的解析引擎
 * 
 * @package Tiny\MVC\View\Engine
 * @since 2013-5-25上午08:21:38
 * @final 2013-5-25上午08:21:38
 */
class Template extends Base
{

    /**
     * 变量正则
     * 
     * @var string
     */
    protected $var_regexp = "\@?\\\$[a-zA-Z_]\w*(?:\[[\w\.\"\'\[\]\$]+\])*";

    /**
     * 对象正则
     * 
     * @var string
     */
    protected $object_regexp = "\@?\\\$[a-zA-Z_]\w*(?:\[[\w\.\"\'\[\]\$]+\])*(\-\>\(.*?)*";

    /**
     * 标签正则
     * 
     * @var string
     */
    protected $vtag_regexp = "\<\?=(\@?\\\$[a-zA-Z_]\w*(?:\[[\w\.\"\'\[\]\$]+\])*)\?\>";

    /**
     * 常量正则
     * 
     * @var string
     */
    protected $const_regexp = "\{([\w]+)\}";

    /**
     * 获取输出的HTML内容
     * 
     * @param void
     * @return string
     *
     */
    public function fetch($file, $isAbsolute = false)
    {
        ob_start();
        extract($this->_variables, EXTR_SKIP);
        include $this->_getCompilePath($file, $isAbsolute);
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

    /**
     * 获取模板解析后的文件路径
     * 
     * @param $file string 文件路径
     * @return string $path
     */
    private function _getCompilePath($file, $isAbsolute = false)
    {
        $path = $isAbsolute ? $file : $this->_templateFolder . $file;
        
        if (! is_file($path))
        {
            throw new ViewerException("viewer error: the template file $path is not exists!");
        }
        $compilePath = $this->_compileFolder . md5($path) . '.php';
        if (file_exists($compilePath) && filemtime($compilePath) > filemtime($path))
        {
         	return $compilePath;
        }
        
        if (! $fh = fopen($path, 'rb'))
        {
            throw new ViewerException("viewer error: fopen $path is faild");
        }
        
        flock($fh, LOCK_SH);
        $templateContent = fread($fh, filesize($path));
        flock($fh, LOCK_UN);
        fclose($fh);
        
        $compileContent = $this->_parseTemplate($templateContent);
        $ret = file_put_contents($compilePath, $compileContent, LOCK_EX);
        if (! $ret)
        {
            throw new ViewerException("viewer compile error: file_put_contents $compilePath is faild");
        }
        return $compilePath;
    }

    /**
     * 解析模板文件
     * 
     * @param void
     * @return string
     *
     */
    private function _parseTemplate($template)
    {
    	

        $template = preg_replace("/\<\!\-\-\{(.+?)\}\-\-\>/s", "{\\1}", $template);
        $template = preg_replace_callback("/\{url\|([^\}|]+)\|?([a-z]+)?\}/is", array($this ,"_resolvUrl"), $template);
        $template = preg_replace("/\{(" . $this->var_regexp . "(\->.*?)*)\}/", "<?=\\1?>", $template);
        $template = preg_replace("/\{(" . $this->const_regexp . ")\}/", "<?=\\1?>", $template);
        $template = preg_replace("/(?<!\<\?\=|\\\\)$this->var_regexp/", "<?=\\0?>", $template);
        $template = preg_replace_callback("/\<\?=(\@?\\\$[a-zA-Z_]\w*)((\[[\\$\[\]\w]+\])+)\?\>/is", array($this ,"_arrayindex"), $template);
        $template = preg_replace_callback("/\{\{eval (.*?)\}\}/is", array($this ,"_stripEvalTag"), $template);
        $template = preg_replace_callback("/\{eval (.*?)\}/is", array($this ,"_stripEvalTag"), $template);
        $template = preg_replace_callback("/\{for (.*?)\}/is", array($this , '_stripForTag'), $template);
        $template = preg_replace_callback("/\{elseif\s+(.+?)\}/is", array($this, "_stripElseIfTag"), $template);
        $template = preg_replace_callback("/\{date\s+(.+?)\}/is", array($this ,"_date"), $template);
        
        for ($i = 0; $i < 4; $i ++)
        {
            $template = preg_replace_callback("/\{loop\s+$this->vtag_regexp\s+$this->vtag_regexp\s+$this->vtag_regexp\}(.+?)\{\/loop\}/is", array($this ,"_loopsection"), $template);
            $template = preg_replace_callback("/\{loop\s+$this->vtag_regexp\s+$this->vtag_regexp\}(.+?)\{\/loop\}/is", array($this ,"_dLoopsection"), $template);
        }
        $template = preg_replace_callback("/\{if\s+(.+?)\}/is", array($this ,"_stripIfTag"), $template);
        $template = preg_replace("/\{template\s+(\w+?)\}/is", "<? include \$this->_getCompilePath('\\1');?>", $template);
        $template = preg_replace_callback("/\{template\s+(.+?)\}/is", array($this ,"_stripvIncludeTag"), $template);
        $template = preg_replace("/\{else\}/is", "<? } else { ?>", $template);
        $template = preg_replace("/\{\/if\}/is", "<? } ?>", $template);
        $template = preg_replace("/\{\/for\}/is", "<? } ?>", $template);
        $template = preg_replace("/$this->const_regexp/", "<?=\\1?>", $template);

        $template = "<? if(!defined('IN_TINY_VIEW_TEMPLATE')) exit('Access Denied');?>\r\n$template";
        $template = preg_replace("/(\\\$[a-zA-Z_]\w+\[)([a-zA-Z_]\w+)\]/i", "\\1'\\2']", $template);
        $template = preg_replace("/\<\?(\s{1})/is", "<?php\\1", $template);
        $template = preg_replace("/\<\?\=(.+?)\?\>/is", "<?php echo \\1;?>", $template);
        
        return $template;
    }

    private function _stripEvalTag($match)
    {
        return "<? " .  $this->_stripvtag($match) . ' ?>';
    }
    /**
     * 过滤标签
     * 
     * @param
     *
     * @return
     *
     */
    private function _stripIfTag($match)
    {
        return '<? if(' . $this->_stripvtag($match) . ') { ?>';
    }

    private function _stripForTag($match)
    {
        return '<? for (' . $this->_stripvtag($match) . ') { ?>';
    }
    private function _stripElseIfTag($match)
    {
        return '<? } elseif(' . $this->_stripvtag($match) . ') { ?>';
    }
    /**
     * 剥离include
     * 
     * @param
     *
     * @return
     *
     */
    private function _stripvIncludeTag($match)
    {	
        return '<? include $this->_getCompilePath("' . $this->_stripvtag($match) . '"); ?>';
    }

    /**
     * 解析数组索引
     * 
     * @param $name string 索引名称
     * @param $items array 解析成的实体
     * @return void
     *
     */
    private function _arrayindex($match)
    {
        $name = $match[1];
        $items = $match[2];
        $items = preg_replace("/\[([a-zA-Z_]\w*)\]/is", "['\\1']", $items);
        return "<?=${name}${items}?>";
    }

    /**
     * 解析脚本标签
     * 
     * @param $s string 标识符
     * @return string
     *
     */
    private function _stripvtag($match)
    {
        $s = $match[1];
        return $this->_doStripvtag($s);
    }

    /**
     * 变量标签
     * 
     * @param string $s
     * @return string
     */
    private function _doStripvtag($s)
    {
        return preg_replace("/$this->vtag_regexp/is", "\\1", str_replace("\\\"", '"', $s));
    }

    /**
     * 解析时间标签
     * 
     * @param string $s 字符串
     * @return void
     */
    private function _date($match)
    {
    	
        $s = $match[1];
        $d = explode('|', $s);
        if (! $d[1])
        {
            $d[1] = 'y-m-d H:i';
        }
        $fromat = $d[1];
        $v = trim($this->_doStripvtag($d[0]));
        return "<? echo date(\"$fromat\", $v)?>";
    }

    /**
     * 循环标签
     * 
     * @param string
     * @return void
     */
    private function _dLoopsection($match)
    {
        return $this->_loopsection(array($match[1] ,'' ,$match[2] ,$match[3]));
    }

    /**
     * 解析遍历数组循环
     * 
     * @param void
     * @return string
     *
     */
    private function _loopsection($match)
    {
        $arr = $this->_doStripvtag($match[1]);
        $k = $this->_doStripvtag($match[2]);
        $v = $this->_doStripvtag($match[3]);
        $statement = $match[4];
        return $k ? "<? foreach((array)$arr as $k => $v) {?>$statement<? }?>" : "<? foreach((array)$arr as $v) {?>$statement<? } ?>";
    }

    /**
     * 解析URL模板
     * 
     * @param string $param URL
     * @param string $type 模板类型
     * @return string
     */
    private function _resolvUrl($match)
    {
        $param = $match[1];
        $type = $match[2];
        $qs = array();
        $params = explode(',', $param);
        $ps = array();
        if (is_array($params))
        {
            foreach ($params as $k => $v)
            {
                $vs = explode('=', $v);
                $ps[$vs[0]] = $vs[1];
            }
        }
        return Url::get($ps, $type);
    }
}
?>