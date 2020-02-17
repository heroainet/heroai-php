<?php
/**
 * @Copyright (C), 2013-, King.
 * @Name Pinyin.php
 * @Author King
 * @Version 1.0
 * @Date: 2013-12-6上午05:59:32
 * @Description
 * @Class List
 * @Function
 * @History <author> <time> <version > <desc>
 * king 2013-12-6上午05:59:32 1.0 第一次建立该文件
 */
namespace Tiny\String;

/**
 * 汉字转拼音类
 * @package Tiny.String
 * @since 2013-12-6上午06:01:17
 * @final 2013-12-6上午06:01:17
 */
class Pinyin
{

	/**
	 * 是否将拼音文件读取到内存内，损耗少许内存,几百KB的样子，速度可以略有提升
	 * 
	 * @var bool
	 */
	public static $isMemoryCache = true;

	/**
	 * 拼音格式转换数据文件
	 * 
	 * @var string
	 */
	protected static $_path =  __DIR__ . '/__res/py.qdb';

	/**
	 * 缓存到内存的字符串
	 * 
	 * @var string
	 */
	protected static $_memoryCache = '';

	/**
	 * 打开的文件句柄
	 * 
	 * @var resource 资源句柄
	 */
	protected static $_fh;

	/**
	 * 错误数组
	 * 
	 * @var array
	 */
	protected static $_errorMsgBox = array ();

	/**
	 * 转换结果
	 * 
	 * @var string
	 */
	protected static $_result;

	/**
	 * 汉字对拼音的转换缓存数组
	 * 
	 * @var array
	 */
	protected static $_words = array ();

	/**
	 * 带音标和不带音标的元音对照表
	 * 
	 * @var array
	 */
	protected static $_nt = array ('ā' => 'a','á' => 'a','ǎ' => 'a','à' => 'a',
			'ɑ' => 'a','ō' => 'o','ó' => 'o','ǒ' => 'o','ò' => 'o','ē' => 'e',
			'é' => 'e','ě' => 'e','è' => 'e','ê' => 'e','ī' => 'i','í' => 'i',
			'ǐ' => 'i','ì' => 'i','ū' => 'u','ú' => 'u','ǔ' => 'u','ù' => 'u',
			'ǖ' => 'v','ǘ' => 'v','ǚ' => 'v','ǜ' => 'v','ü' => 'v');

	/**
	 * 转换拼音
	 *
	 * @param string $str 所需转换字符
	 * @param bool $isToneMark 是否保留音标 默认为false
	 * @param bool $isFirst 是否只保留首字母 默认为false
	 * @param string $suffix 尾缀,默认为空格
	 * @param string $charset 编码  默认为utf-8
	 * @return string
	 */
	public static function transform($str, $isToneMark = false, $isFirst = false, $suffix = ' ', $charset = 'utf-8')
	{
		if ('utf-8' == $charset)
		{
			$str = iconv('utf-8', 'GB2312//IGNORE', $str);
		}
		if (! is_array($str))
		{
			return self::_topy($str, $charset, $suffix, $isToneMark, $isFirst);
		}
		foreach ($str as & $val)
		{
			$val = self::_topy($val, $charset, $suffix, $isToneMark, $isFirst);
		}
		return $str;
	}

	/**
	 * 拼音转换
	 * 
	 * @param string $str 所需转换字符
	 * @param bool $isToneMark 是否保留音标 默认为false
	 * @param bool $isFirst 是否只保留首字母 默认为false
	 * @param string $suffix 尾缀,默认为空格
	 * @return string
	 * @return string
	 */
	protected static function _topy($str, $charset, $suffix, $isToneMark, $isFirst)
	{
		self::$_result = '';
		if ('' == trim($str))
		{
			return '';
		}
		if (self::$isMemoryCache)
		{
			self::$_result = self::_toByMemory($str, $suffix, $isFirst);
		}
		else
		{
			self::$_result = self::_toByIo($str, $suffix, $isFirst);
		}
		if ('utf-8' == $charset)
		{
			self::$_result = iconv('gbk', 'utf-8', self::$_result);
		}
		if (! $isToneMark)
		{
			self::$_result = strtr(self::$_result, self::$_nt);
		}
		return self::$_result;
	}

	/**
	 * 在内存里转换
	 * 
	 * @param string $str 待转换的字符串
	 * @return string
	 */
	protected static function _toByMemory($str, $suffix, $isFirst)
	{
		$result = '';
		if (! self::$_memoryCache)
		{
			if (! is_file(self::$_path))
			{
				throw new \Exception('路径:' . self::$_path . '不存在!', E_WARNNING);
			}
			self::$_memoryCache = file_get_contents(self::$_path);
		}
		$strLength = strlen($str);
		for ($i = 0; $i < $strLength; $i++)
		{
			$ord1 = ord(substr($str, $i, 1));
			if ($ord1 > 128)
			{
				$ord2 = ord(substr($str, ++ $i, 1));
				if (! isset(self::$_words[$ord1][$ord2]))
				{
					$leng = ($ord1 - 129) * ((254 - 63) * 8 + 2) + ($ord2 - 64) * 8;
					self::$_words[$ord1][$ord2] = trim(substr(self::$_memoryCache, $leng, 8));
				}
				$strtrLen = $isFirst ? 1 : 8;
				$result .= substr(self::$_words[$ord1][$ord2], 0, $strtrLen) . $suffix;
			}
			else
			{
				$result .= substr($str, $i, 1);
			}
		}
		return $result;
	}

	/**
	 * 通过IO流转换拼音
	 * 
	 * @param string $str 待转换的字符串
	 * @return string
	 */
	protected static function _toByIO($str, $suffix, $isFirst)
	{
		$result = '';
		$strLength = strlen($str);
		self::$_fh = fopen(self::$_path, 'r');
		for ($i = 0; $i < $strLength; $i++)
		{
			$ord1 = ord(substr($str, $i, 1));
			if ($ord1 > 128)
			{
				$ord2 = ord(substr($str, ++ $i, 1));
				if (! isset(self::$_words[$ord1][$ord2]))
				{
					$leng = ($ord1 - 129) * ((254 - 63) * 8 + 2) + ($ord2 - 64) * 8;
					fseek(self::$_fh, $leng);
					self::$_words[$ord1][$ord2] = trim(fgets(self::$_fh, 8));
				}
				$strtrLen = $isFirst ? 1 : 8;
				$result .= substr(self::$_words[$ord1][$ord2], 0, $strtrLen) . $suffix;
			}
			else
			{
				$result .= substr($str, $i, 1);
			}
		}
		return $result;
	}
}
?>