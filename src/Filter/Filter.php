<?php 
namespace Tiny\Filter;

use Tiny\Mvc\Request\Base as Request;
use Tiny\Mvc\Response\Base as Response;

/**
 * 
 * @author root
 *
 */
class Filter implements IFilter
{
    /**
     * 获取单例实例
     * 
     * @var Filter
     */
    protected static $_instance;
    
    /**
     * 过滤器集合
     * 
     * @var array
     */
    protected $_filters = [];
    
    /**
     * 获取单例实例
     * @return Filter
     */
    public static function getInstance()
    {
        if (!self::$_instance)
        {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    /**
     * 添加过滤器
     * 
     * @param IFilter $filter
     */
    public function addFilter($filterName)
    {
        if (!class_implements($filterName)  == "IFilter")
        {
            return;
        }
        if ($this->_filters[$filterName])
        {
            return;
        }
        $filter = new $filterName();
        $this->_filters[$filterName] = $filter;
    }
    
    /**
     * 过滤
     * @param Request $req
     * @param Response $res
     */
    public function doFilter(Request $req, Response $res)
    {
        foreach($this->_filters as $filter)
        {
            $filter->doFilter($req, $res);
        }
    }
    
    /**
     * 格式化成json
     * @param int $status
     * @param mixed $msg
     * @param mixed $data
     * @return []
     */
    public function formatJSON($status, $msg, $data)
    {
        return ['status' => $status, 'msg' => $msg, 'data' => $data];
    }
    
    /**
     * 格式化int
     * @param int $int
     * @param int $min
     * @param int $max
     * @return int
     */
    public function formatInt($int, int $min = NULL, int $max = NULL):int
    {
        $int = (int)$int;
        if ($min !== NULL && $int < $min)
        {
            $int = $min;
        }
        if ($max !== NULL && $int > $max)
        {
            $int = $max;
        }
        return $int;
    }
    
    /**
     * 格式化成string
     * @param string $str
     */
    public function formatString($str, $default = NULL)
    {
        return (string)$str ?: $default;
    }
    
    /**
     * 全部小写
     * @param string $str
     * @param string $default
     * @return string
     */
    public function formatLower($str, $default = NULL)
    {
        $str = strtolower($str);
        return $this->formatString($str, $default);
    }
    
    /**
     * 全部大写
     * @param string $str
     * @param string $default
     * @return string
     */
    public function formatUpper($str, $default = NULL)
    {
        $str = strtoupper($str);
        return $this->formatString($str, $default);
    }
    
    /**
     * 防注入和XSS攻击
     * @param mixed $data
     * @return NULL[][] mixed
     */
    public function formatWeb($data)
    {
      if (is_array($data))
      {
          $ndata = [];
          foreach($data as $k => $v)
          {
              $ndata[$k] = $this->formatWeb($v);
          }
          return $ndata;
      }
      $data = htmlspecialchars($data);
      $data = preg_replace('/^(select|insert|and|or|create|update|delete|alter|count|\'|\/\*|\*|\.\.\/|\.\/|union|into|load_file|outfile)/i', '', $data);
      $data = addslashes($data);
      return $data;
    }
    
    /**
     * 去除html标签
     * @param string $str
     * @param string $tags
     * @return string
     */
    public function formatStripTags($str, $tags = NULL)
    {
        return strip_tags($str, $tags);
    }
    
    /**
     * 去除空格
     * @param string $str
     * @return string
     */
    public function formatTrim($str)
    {
        return trim($str);
    }
    
    /**
     * 
     * @param string $method
     * @param array $args
     */
    public function __call($method, $args)
    {
        if (!(strlen($method) > 6 && 'format' == substr($method, 0, 6)))
        {
            return $args;
        }
        
        foreach($this->_filters as $filter)
        {
            if (!method_exists($filter, $method))
            {
                continue;
            }
            return call_user_func_array(array($filter, $method), $args);
        }
    }
    
    /**
     * 限制单例
     */
    protected function __construct()
    {
        
    }
}
?>