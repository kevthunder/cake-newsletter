<?php
class NewsletterAppController extends AppController {
	var $pluginVersion = "2.6.0"; 
	
	var $view = "Newsletter";
	
	function __construct() {
		App::import('Lib', 'Newsletter.NewsletterConfig');
		NewsletterConfig::load();
		App::import('Vendor', 'Newsletter.newsletter_view');
		parent::__construct();
		
		App::import('Lib', 'Newsletter.ClassCollection');
	}
	
	function constructClasses() {
	
	
		if(!empty($this->params['admin']) && $this->params['controller'] != 'newsletter_upgrade' && !NewsletterConfig::checkSchema()) {
			$this->redirect(array('plugin'=>'newsletter','controller'=>'newsletter_upgrade','action'=>'upgrade','admin'=>true));
		}
		
		return parent::constructClasses();
	}
	
	function beforeFilter() {
		parent::beforeFilter();
		//Configure::write('debug', 1);
	}
}
?>