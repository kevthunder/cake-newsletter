<?php
class NewsletterConfig extends Object {
	/*
		App::import('Lib', 'Newsletter.NewsletterConfig');
		NewsletterConfig::load();
	*/
	
	var $loaded = false;
	var $defaultConfig = array(
		'selfSending' => false,
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
			Configure::write('Newsletter',$config);
			$_this->loaded = true;
		}
		if(!empty($path)){
			return Configure::read('Newsletter'.($path!==true?'.'.$path:''));
		}
	}
	
}
?>