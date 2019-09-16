<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * Typecho文章静态化插件
 *
 * @package TpHtmlCache
 * @author huhaku
 * @version 1.1.0
 * @link https://ggdog.info
 */
class TpHtmlCache_Plugin implements Typecho_Plugin_Interface
{
	/* public static function recursiveDelete($dir)
	{    
		 
	   if ($handle = @opendir($dir))
	   {
		 while (($file = readdir($handle)) !== false)
		 {
			 if (($file == ".") || ($file == ".."))
			 {
			   continue;
			 }
			 if (is_dir($dir . '/' . $file))
			 {
			   recursiveDelete($dir . '/' . $file);
			 }
			 else
			 {
			   unlink($dir . '/' . $file); 
			 }
		 }
		 @closedir($handle);
		 rmdir ($dir); 
	   }
	} */
	
    public static function activate()
    {
        //页面收尾
        Typecho_Plugin::factory('index.php')->begin = array('TpHtmlCache_Plugin', 'Start');
        Typecho_Plugin::factory('index.php')->end = array('TpHtmlCache_Plugin', 'Ends');
		$dir=__DIR__."/cache/";//缓存目录
		if(!file_exists($dir)) {
			mkdir($dir,0777,true);
		}else{
			chmod($dir,0777);
		} 	
		
		file_put_contents($dir.'index.html',"error 403");
		
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
		// self::recursiveDelete(__DIR__."/cache/");
		return '插件卸载成功,请手动清理缓存目录';
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
		$allow_path = new Typecho_Widget_Helper_Form_Element_Text('allow_path', NULL, NULL, _t('需要缓存的路径,英文逗号分隔,从前往后匹配'));
        $form->addInput($allow_path);
		$cache_time = new Typecho_Widget_Helper_Form_Element_Text('cache_time', NULL, NULL, _t('缓存时间,为0则禁用缓存'));
        $form->addInput($cache_time);
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
		$config = json_decode(json_encode(unserialize(Helper::options()->plugin('TpHtmlCache'))));
		if(empty($config->allow_path) || !is_writable(__DIR__."/cache/")) {
			if (Typecho_Widget::widget('Widget_User')->hasLogin()){
			
			if(!is_writable(__DIR__."/cache/")){
				echo '<span style="text-align: center;display: block;margin: auto;font-size: 1.5em;color:#ff0000">设置目录权限失败,cache目录似乎不可写</span>';
			}
			if(empty($config->allow_path)){
				$options = Typecho_Widget::widget('Widget_Options');
				$config_url = trim($options->siteUrl,'/').'/'.trim(__TYPECHO_ADMIN_DIR__,'/').'/options-plugin.php?config=TpHtmlCache';
				echo '<span style="text-align: center;display: block;margin: auto;font-size: 1.5em;color:#1abc9c">你似乎还没有初始化缓存插件，<a href="'.$config_url.'">马上去设置</a></span>';
				}
			}else{
				return '';
			}
        }else{
			//已登录用户不缓存
			if(Typecho_Widget::widget('Widget_User')->hasLogin()) return '';
			//过期时间设置为0禁用缓存
			if($config->cache_time == 0 || empty($config->cache_time)) return '';
			
			if(self::needCache($_SERVER["REQUEST_URI"])){
				return '';
			}else{
				$expire = ($config->cache_time == '' || $config->cache_time == null) ? 86400 : $config->cache_time;
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
	}
	
    /**
     * 缓存后置操作
     */
    public static function Ends()
    {
		$config = json_decode(json_encode(unserialize(Helper::options()->plugin('TpHtmlCache'))));
		if(empty($config->allow_path)) {
			return '';
		}else{
			
			if(Typecho_Widget::widget('Widget_User')->hasLogin()) return '';
			//过期时间设置为0禁用缓存
			if($config->cache_time == 0 || empty($config->cache_time)) return '';
			
			if(self::needCache($_SERVER["REQUEST_URI"])){
				return '';
			}else{
			$files=mb_substr(md5($_SERVER["REQUEST_URI"]),0,2);
			$file=__DIR__."/cache/".$files."/".md5($_SERVER["REQUEST_URI"]).".html";//文件路径
			$html=ob_get_contents()."<!--TpHtmlCache ".date("Y-m-d h:i:s")."-->";
				file_put_contents($file,$html);
			}
		}
    }
	
	/**
     * 根据配置判断是否需要缓存
     * @param string 路径信息
     * @return bool
     */
    public static function needCache($path)
    {
		$config = json_decode(json_encode(unserialize(Helper::options()->plugin('TpHtmlCache'))));
		if(empty($config->allow_path)) {
			return '1';	
		}else{
			$allow_paths = explode(',',str_replace('，',',',$config->allow_path));
			foreach($allow_paths as $paths){
				if(strstr($path,$paths)){
					return '0';
					break;
				} 
			}
			return '1';
		}	
    }
}
