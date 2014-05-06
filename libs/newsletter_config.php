<?php
class NewsletterConfig extends Object {
	/*
		App::import('Lib', 'Newsletter.NewsletterConfig');
		NewsletterConfig::load();
	*/
	
	var $loaded = false;
	var $defaultConfig = array(
		'defaultSendlist' => null,
		'orderedSendlist' => false,
		'EmailAdd'=>array(
			'confirm' => true,
		),
		'selfSending' => false,
		'sender' => 'Newsletter.PhpMail', //Newsletter.PhpMail, Newsletter.Mailgun
		'maxSend' => null, //defaults to 10000 for PhpMail and 200 000 for Mailgun
		'langs' => array(), //array('fre'=>'Français','eng'=>'English')
		'bounceLimit' => 3,
		'newsletterSyncTimout'=>'7 DAY',
		'hiddenTemplates' => array(),
		'multimedia' => true,
		'cron' => 'auto',
		'contentUrl' => false,
		'oldSerializeUTF8' => false,
	);
	
	var $defZoneOpt = array(
		'ordered' => true,
		'delete' => true,
		'boxList' => array(),
	);
	
	//$_this =& NewsletterConfig::getInstance();
	function &getInstance() {
		static $instance = array();
		if (!$instance) {
			$instance[0] =& new NewsletterConfig();
		}
		return $instance[0];
	}
	
	function load($path = true){
		$_this =& NewsletterConfig::getInstance();
		if(!$_this->loaded){
			config('plugins/newsletter');
			$config = Configure::read('Newsletter');
			$config = Set::merge($_this->defaultConfig,$config);
			$config['_cronAuto'] = ($config['cron'] === 'auto');
			if($config['_cronAuto']){
				Cache::config('cron_cache', array(
					'engine' => 'File',
					'duration'=> '+1 week',
					'path' => CACHE,
				));
				$config['cron'] = !!Cache::read('newsletter_autocron','cron_cache');
			}
			if($config['contentUrl'] && $config['contentUrl'][strlen($config['contentUrl'])-1] != '/'){
				$config['contentUrl'] .= '/'; 
			}
			Configure::write('Newsletter',$config);
			$_this->loaded = true;
			//debug($config);
		}
		if(!empty($path)){
			return Configure::read('Newsletter'.($path!==true?'.'.$path:''));
		}
	}
	
	
	function getDefZoneOpt(){
		$_this =& NewsletterConfig::getInstance();
		return $_this->defZoneOpt;
	}
	
	function getNewsletterBoxPaths($template,$newsletterTemplate=null,$editMode=false,$fullPath=false,$single=false){
		$action = $template.($editMode?'_edit':'');
		$paths = array();
		$start = '';
		if($fullPath) $start = DS.'elements'.DS;
		if($newsletterTemplate){
			if($single) $paths[] = $start.'newsletter_box'.DS.$newsletterTemplate.DS.'single'.DS.$action;
			$paths[] = $start.'newsletter_box'.DS.$newsletterTemplate.DS.$action;
		}
		if($single) $paths[] = $start.'newsletter_box'.DS.'single'.DS.$action;
		$paths[] = $start.'newsletter_box'.DS.$action;
		return $paths;
	}
	
	function getTemplatesConfig(){
		$_this =& NewsletterConfig::getInstance();
		if(!isset($_this->templates)){
			App::import('Lib', 'Newsletter.ClassCollection');
			
			uses('Folder');
			$Folder =& new Folder();
			
			$paths = $_this->getAllViewPaths();
			foreach($paths as $path) {
				if($Folder->cd($path.'/elements/newsletter')){
					$templateFiles = $Folder->find('.+\.ctp$');
					foreach($templateFiles as &$file){
						$name = basename($file, ".ctp");
						$config = ClassCollection::getObject('NewsletterConfig',$name);
						$config->path = $Folder->path.DS.$file;
						$templates[$name] = $config;
					}
				}
			}
			$_this->templates = $templates;
		}
		return $_this->templates;
	}
	
	function getAllViewPaths(){
		$_this =& NewsletterConfig::getInstance();
		if(!isset($_this->allViewPaths)){
		
			uses('Folder');
			$Folder =& new Folder();
		
			$templates = array();
			$paths = App::path('views');
			$pluginsPaths = App::path('plugins');
			foreach($pluginsPaths as $path) {
				if($Folder->cd($path)){
					$pluginPaths = $Folder->read();
					foreach($pluginPaths[0] as $pluginPath){
						array_push($paths,$path.$pluginPath.DS.'views'.DS);
					}
				}
			}
			$_this->allViewPaths = $paths;
		}else{
			$paths = $_this->allViewPaths;
		}
		return $paths;
	}
	
}
?>