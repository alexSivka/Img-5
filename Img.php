<?php

require_once 'SimpleImage.php';


/**
 * class Img for resize and watermark images with cache engine.
 * this version works with php 5.0 and high
 * usage
 * Img::get('path/to/file.jpg', array('width' => 200))
 * or simple Img::get('path/to/file.jpg', $width, [$height, [$crop]])
 */
class Img {

	/** @var string $docRoot DOCUMENT ROOT path. If not defined, will be used $_SERVER['DOCUMENT_ROOT'] */
	private static $docRoot;

	/**
	 * path to directory where resized images should be placed. If not defined,
	 * cache directory(with name "thumbs") will be created in source image directory.
	 * @var string $cacheDirectory
	 */
	private static $cacheDirectory = '';
	
	private static $placeholder;

	/**
	 * Default values
	 * @var array $defaults
	 * @param integer width - with to resized
	 * @param integer height - height to resized
	 * @param mixed crop - boolean or number(1/0) or string with crop position center|top|left|bottom|right|top left|top right|bottom left|bottom right
	 * @param string watermark - path to watermark file_ext
	 * @param string wm_position - watermark position center|top|left|bottom|right|top left|top right|bottom left|bottom right
	 * @param float wm_opacity - opacity of watermark or watermark text
	 * @param integer quality - quality for result images
	 * @param string wm_text - text for overlay on images
	 * @param mixed wm_text_color - color of watermark text,
	 * 							maybe string('#FFFFFF') or array ['r'=>255,'g'=>255, 'b'=> 255, 'a'=>1] or array [255, 255, 255, 1]
	 * @param integer wm_text_size - font size of watermark text
	 * @param string wm_text_font - name of font for watermark text, the font file mast be in same directory with Img.php
	 * @param string default - path to placeholder, if defined and source image does not exists, this file will be converted
	 */
	public static $defaults = array(
		'width' => 0,
		'height' => 0,
		'crop' => false,
		'watermark' => '',
		'wm_position' => 'center',
		'wm_opacity' => 0.6,
		'quality' => 80,
		'wm_text' => '',
		'wm_text_color' => '#FFFFFF',
		'wm_text_size' => 64,
		'wm_text_font' => 'arial.ttf',
		'default' => ''
	);

	/**
	 * short aliases for parameters
	 * @var array $aliases
	 */
	public static $aliases = array(
		'w' => 'width',
		'h' => 'height',
		'c' => 'crop',
		'wm' => 'watermark',
		'wmp' => 'wm_position',
		'wmo' => 'wm_opacity',
		'q' => 'quality',
		'wmt' => 'wm_text',
		'wmtc' => 'wm_text_color',
		'wmts' => 'wm_text_size',
		'wmtf' => 'wm_text_font',
		'd' => 'default'
	);

	/**
	 * @param  string $fileName source file path
	 * @param  array  $params   parameters for resize
	 * @return string relative path to new file
	 */
	public static function get($fileName, $params = array(), $height = 0, $crop = false){

		if(!is_array($params)){
			$params = array(
				'width' => $params,
				'height' => $height,
				'crop' => $crop
			);
		}

		$params = self::setParams($params);

		if(!$filepath = self::checkFilepath($fileName, $params)) return '';
		$newFilename = self::getNewFileName($filepath, $params);

		$filepath = self::getAbsolutePath($filepath);
		$srcTime = filemtime($filepath);

		if(is_file($newFilename) && $srcTime == filemtime($newFilename) ) return self::getRelativeLink($newFilename);

		$img = new SimpleImage($filepath);

		if($params['width'] && $params['height']){
			if($params['crop']) $img->thumbnail($params['width'], $params['height'], $params['crop']);
			else $img->best_fit($params['width'], $params['height']);
		}elseif($params['width'] && !$params['height']){
			$img->fit_to_width($params['width']);
		}elseif(!$params['width'] && $params['height']){
			$img->fit_to_height($params['height']);
		}

		if($params['watermark']) self::setWatermark($img, $params);
		if($params['wm_text']) self::setWatermarkText($img, $params);

		$img->save($newFilename, $params['quality']);

		touch($newFilename, $srcTime);

		return self::getRelativeLink($newFilename);

	}

	/**
	 * put watermark on image
	 * @param object $img
	 * @param array $params
	 * @return void
	 */
	private static function setWatermark($img, $params){
		$watermark = self::getAbsolutePath($params['watermark']);
		if(!is_file($watermark)) return;
		$img->overlay($watermark, $params['wm_position'], $params['wm_opacity']);
	}

	/**
	 * put watermark text on image
	 * @param object $img
	 * @param array $params
	 * @return void
	 */
	private static function setWatermarkText($img, $params){
		$fontPath = dirname(__FILE__) . '/' . $params['wm_text_font'];
		$color = self::normalizeColor($params['wm_text_color'], $params['wm_opacity']);
    	$img->text($params['wm_text'], $fontPath, $params['wm_text_size'], array($color), $params['wm_position']);
	}

	/**
	 * create color array
	 * @param mixed $color
	 * @param integer $opacity
	 * @return array
	 */
	private static function normalizeColor($color, $opacity = 0){
		if(!is_string($color)) return $color;
		$color = ltrim($color, '#');
		if(strlen($color) == 3) $color = $color[0] . $color[0] . $color[1] . $color[1] . $color[2] . $color[2];
		return array(
			'r' => hexdec($color[0] . $color[1]),
			'g' => hexdec($color[2] . $color[3]),
			'b' => hexdec($color[4] . $color[5]),
			'a' => 127 - ($opacity * 127)
		);
	}

	/**
	 * generate new file path
	 * @param string $filepath
	 * @param array $params
	 * @return string
	 */
	private static function getNewFileName($filepath, $params){
		$fileinfo = pathinfo($filepath);
		$thumbnailDir = self::getCacheDirectory($fileinfo['dirname']);
		$thumbnailDir .= $params['width'] . 'x' . $params['height'];
		if($params['crop']) $thumbnailDir .= 'cp';
		if($params['wm_text']) $thumbnailDir .= 'wmt' . $params['wm_opacity'];
		if($params['watermark']) $thumbnailDir .= 'w' . $params['wm_opacity'];

		if($params['watermark'] || $params['wm_text']){
			$wmp = preg_split('~[ ]+~', $params['wm_position'], -1, PREG_SPLIT_NO_EMPTY);
			$wmp = isset($wmp[1]) ? $wmp[0][0] . $wmp[1][0] : $wmp[0][0];
			$thumbnailDir .= $wmp;
		}

		$thumbnailDir .= '_' . $params['quality'];
		if (!file_exists($thumbnailDir)) mkdir($thumbnailDir);
		return $thumbnailDir . '/' . $fileinfo['filename'] . '.' . $fileinfo['extension'];
	}

	/**
	 * create cache directory
	 * @param string $fileDir
	 * @return string
	 */
	private static function getCacheDirectory($fileDir){
		if(!self::$cacheDirectory){
			$thumbnailDir = self::getAbsolutePath($fileDir . '/thumbs');
			if (!file_exists($thumbnailDir)) mkdir($thumbnailDir);
			return $thumbnailDir .= '/';
		}
		$path = self::getRelativeLink(self::$cacheDirectory) . self::getRelativeLink($fileDir);
		$dirs = preg_split('~[/]~', $path, -1, PREG_SPLIT_NO_EMPTY);

		$thumbnailDir = self::getDocRoot();

		foreach($dirs as $dir){
			$thumbnailDir .= '/' . $dir;
			if(!is_dir($thumbnailDir)) mkdir($thumbnailDir);
		}
		return $thumbnailDir .= '/';
	}

	/**
	 * check if source file exists
	 * @param string $filepath
	 * @param array $params
	 * @return string
	 */
	private static function checkFilepath($filepath, $params){
		return  is_file( self::getAbsolutePath( $filepath ) ) ? $filepath : self::getDefaultImg($params);
	}

	/**
	 * returns placeholder img if source imf not exists
	 * @param array $params
	 * @return string
	 */
	private static function getDefaultImg($params){
		if(self::$placeholder) return self::$placeholder;
		return $params['default'] && is_file(self::getAbsolutePath($params['default'])) ? $params['default'] : '';
	}
	
	/**
	 * set placeholder image
	 * @param string $filepath
	 * @return void
	 */
	public static function setPlaceholder($filepath){
		self::$placeholder = $filepath;
	}

	/**
	 * calculate relative path of file
	 * @param string $path
	 * @return string
	 */
	private static function getRelativeLink($path){
		$docRoot = self::getDocRoot();
	    if (substr($path, 0, strlen($docRoot)) == $docRoot) $path = substr($path, strlen($docRoot));
		return '/' . ltrim($path, '/');
	}

	/**
	 * calculate absolute path of file
	 * @param string $path
	 * @return string
	 */
	private static function getAbsolutePath($path){
		return self::getDocRoot() . '/' . ltrim( self::getRelativeLink($path), '/' );
	}

	/**
	 * setup params
	 * @param array $params
	 * @return array
	 */
	public static function setParams($params){
		$params = self::setFromAliases($params);
		$params = array_merge(self::$defaults, $params);
		if($params['crop'] && !is_string($params['crop'])) $params['crop'] = 'center';
		return $params;
	}

	/**
	 * setup default params
	 * @param array $params
	 * @return void
	 */
	public static function setDefaults($params){
		$params = self::setFromAliases($params);
		self::$defaults = array_merge(self::$defaults, $params);
	}

	/**
	 * set params defined as short aliases
	 * @param array $params
	 * @return array
	 */
	private static function setFromAliases($params){
		foreach($params as $k => $v){
			 if(isset(self::$aliases[$k])){
				 $params[ self::$aliases[$k] ] = $v;
				 unset($params[$k]);
			 }
		}
		return $params;
	}
	
	/**
	 * set cache directory path
	 * @param string $dir
	 * @return void
	 */
	public static function setCacheDirectory($dir){
		$dir = self::getAbsolutePath($dir);
		self::$cacheDirectory = $dir;
	}

	/**
	 * set DOCUMENT ROOT
	 * @param string $docRoot
	 * @return void
	 */
	public static function setDocRoot($docRoot){
		self::$docRoot = rtrim($docRoot, '/');
	}

	/**
	 * get DOCUMENT ROOT
	 * @return string
	 */
	public static function getDocRoot(){
		return self::$docRoot ? self::$docRoot : $_SERVER['DOCUMENT_ROOT'];
	}

}
