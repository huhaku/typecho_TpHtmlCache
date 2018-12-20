<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * Typecho文章静态化插件
 *
 * @package TpHtmlCache
 * @author huhaku
 * @version 1.0.0
 * @link http://www.phpgao.com
 */
class TpHtmlCache_Plugin implements Typecho_Plugin_Interface
{
    public static function activate()
    {
        //页面收尾
        Typecho_Plugin::factory('index.php')->begin = array('TpHtmlCache_Plugin', 'Start');
        Typecho_Plugin::factory('index.php')->end = array('TpHtmlCache_Plugin', 'Ends');
        return '插件安装成功';
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     *
     * @static
     * @access public
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate()
    {
    }

    /**
     * 获取插件配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
		
    }
    /**
     * 个人用户的配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {
    }

    /**
     * 缓存前置操作
     */
    public static function Start()
    {
        //已登录用户不缓存
        if(Typecho_Widget::widget('Widget_User')->hasLogin()) return '';
		if(self::needCache($_SERVER["REQUEST_URI"])){
			return '';
			}
		else{
			$expire = 7776000;
			$files=mb_substr(md5($_SERVER["REQUEST_URI"]),0,2);
			$file=__DIR__."/cache/".$files."/".md5($_SERVER["REQUEST_URI"]).".html";//文件路径
			$dir=__DIR__."/cache/".$files."/";//缓存目录
				if(!file_exists($dir)) {
					mkdir($dir,0777,true);
				} 		
			if (file_exists($file)) {
				$file_time = @filemtime($file);
				if(time()-$file_time<$expire){
					echo file_get_contents($file);//直接输出缓存
					exit();
				}else{
				ob_start();//打开缓冲区
				}
			} else {
				ob_start();//打开缓冲区
			}
		}
		
    }
	
    /**
     * 缓存后置操作
     */
    public static function Ends()
    {
	if(Typecho_Widget::widget('Widget_User')->hasLogin()) return '';
	if(self::needCache($_SERVER["REQUEST_URI"])){
		return '';
		}else{
	$files=mb_substr(md5($_SERVER["REQUEST_URI"]),0,2);
	$file=__DIR__."/cache/".$files."/".md5($_SERVER["REQUEST_URI"]).".html";//文件路径
	$html=ob_get_contents()."<!--TpHtmlCache ".date("Y-m-d h:i:s")."-->";
		file_put_contents($file,$html);
		}
    }
	
	/**
     * 根据配置判断是否需要缓存
     * @param string 路径信息
     * @return bool
     */
    public static function needCache($path)
    {
        if(strstr($path,'/posts/')) 
			return '0';
		else
			return '1';
    }
}
