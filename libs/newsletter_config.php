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
		'sender' => 'Newsletter.PhpMail',
		'maxSend' => null, //defaults to 10000 for PhpMail and 200 000 for Mailgun
		'langs' => array(), //array('fre'=>'Français','eng'=>'English')
		'hiddenTemplates' => array(),
		'multimedia' => true,
		'cron' => 'auto',
		'contentUrl' => false,
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
		}
		if(!empty($path)){
			return Configure::read('Newsletter'.($path!==true?'.'.$path:''));
		}
	}
	
	
	function getDefZoneOpt(){
		$_this =& NewsletterConfig::getInstance();
		return $_this->defZoneOpt;
	}
	
}
?>