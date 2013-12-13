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
	
	
	function checkTables(&$errors = array()){
		$tables = array(
			'newsletter_boxes',
			'newsletter_emails',
			'newsletter_sended',
			'newsletter_sendings',
			'newsletter_sendlists',
			'newsletter_sendlists_emails',
			'newsletter_stats',
			'newsletter_variants',
			'newsletters',
		);
		
		
		App::import('Lib', 'ConnectionManager');
		$db =& ConnectionManager::getDataSource('default');
		
		$sourceList = $db->listSources();
		
		$missing = array_diff($tables,$sourceList);
		if(count($missing)){
			$errors['missing_table'] = array('msg'=>'Missing Table(s)','tables'=>$missing);
			return false;
		}
		
		return empty($errors);
	}
	
	function checkSchema(&$errors = array()){

		if(!NewsletterConfig::checkTables($errors)){
			return false;
		}
		
		$NewsletterEmail = ClassRegistry::init('Newsletter.NewsletterEmail');
		//debug($NewsletterEmail->schema());
		if(array_key_exists('sendlist_id',$NewsletterEmail->schema())){
			$errors['field_mismatch']['fields'][] = 'NewsletterEmail.sendlist_id';
		}
		$NewsletterSended = ClassRegistry::init('Newsletter.NewsletterSended');
		if(array_key_exists('sendlist_id',$NewsletterSended->schema())){
			$errors['field_mismatch']['fields'][] = 'NewsletterSended.sendlist_id';
		}
		if(!array_key_exists('newsletter_variant_id',$NewsletterSended->schema())){
			$errors['field_mismatch']['fields'][] = 'NewsletterSended.newsletter_variant_id';
		}
		if(!array_key_exists('name',$NewsletterSended->schema())){
			$errors['field_mismatch']['fields'][] = 'NewsletterSended.name';
		}
		
		
		if(!empty($errors['field_mismatch'])){
			$errors['field_mismatch']['Msg'] = 'Some field(s) are configured wrong';
		}
		
		return empty($errors);
	}
}
?>