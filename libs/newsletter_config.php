<?php
class NewsletterConfig extends Object {
	/*
		App::import('Lib', 'Newsletter.NewsletterConfig');
		NewsletterConfig::load();
	*/
	
	var $loaded = false;
	var $defaultConfig = array(
		'defaultSendlist' => 1,
		'EmailAdd.confirm' => true,
		'selfSending' => false,
		'sender' => 'Newsletter.PhpMail',
		'maxSend' => null, //defaults to 10000 for PhpMail and 200 000 for Mailgun
		'langs' => array(), //array('fre'=>'Français','eng'=>'English')
		'hiddenTemplates' => array(),
		'multimedia' => true,
		'cron' => 'auto',
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
			Configure::write('Newsletter',$config);
			$_this->loaded = true;
		}
		if(!empty($path)){
			return Configure::read('Newsletter'.($path!==true?'.'.$path:''));
		}
	}
	
}
?>