<?php
class NewsletterAppController extends AppController {
	var $pluginVersion = "0.1.2.5.7"; 
	
	var $view = "Newsletter";
	
	function __construct() {
		App::import('Lib', 'Newsletter.NewsletterConfig');
		NewsletterConfig::load();
		App::import('Vendor', 'Newsletter.newsletter_view');
		parent::__construct();
		
		App::import('Lib', 'Newsletter.ClassCollection');
	}
	/*function beforeFilter() {
		parent::beforeFilter();
		Configure::write('debug', 1);
	}*/
}
?>