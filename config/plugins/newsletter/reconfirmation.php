<?php
class ReconfirmationNewsletterConf extends NewsletterTemplateConfig {
	var $label = 'Reconfirmation';
	
	
	function form($view){
		//debug($view->data);
		$html = '';
		$html .= '<p class="warning">'.__d('newsletter','Any email this newsletter is sent to will be disabled until the user use the link to resubscribe',true).'</p>';
		$html .= $view->Form->input('Newsletter.data.reenable_text',array('type'=>'textarea', 'class'=>'tinymce', 'label'=>__d('newsletter','Resubcribe response text',true)));
		$html .= $view->Form->input('Newsletter.data.decline_text',array('type'=>'textarea', 'class'=>'tinymce', 'label'=>__d('newsletter','Decline response text',true)));
		return $html;
	}
	
	function beforeSend($sender,&$opt,&$mailsOptions){
		$this->beforeSendCreateCodes($mailsOptions);
	}
	
	function afterSend($sender,$opt,$mailsOptions){
		//$this->log($mailsOptions,'newsletter');
		
		$emails = array();
		foreach($mailsOptions as $opt){
			$emails[] = $opt['email']['email'];
		}
		//$this->log($emails,'newsletter');
		
		App::import('Lib', 'Newsletter.Sendlist');
		Sendlist::disable_email($emails);
	}
} 

?>