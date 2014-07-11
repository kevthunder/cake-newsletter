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
		if(!empty($opt['sending']['NewsletterSending']['status']) && $opt['sending']['NewsletterSending']['status'] == 'test'){
			$opt['content'] = str_replace('reenable/%code%/','reenable/newsletter_id:'.$opt['sending']['Newsletter']['id'].'/',$opt['content']);
			$opt['content'] = str_replace('decline/%sended_id%/','decline/newsletter_id:'.$opt['sending']['Newsletter']['id'].'/',$opt['content']);
		}
		$this->beforeSendCreateCodes($mailsOptions,$opt);
	}
	
	function afterSend($sender,$opt,$mailsOptions){
		if(!empty($opt['sending']['NewsletterSending']['status']) && $opt['sending']['NewsletterSending']['status'] == 'test'){
		}else{
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
	
	function beforeView(&$newsletter,$sended,$admin){
		if(!empty($sended['NewsletterSended']['code'])){
			$newsletter['Newsletter']['html'] = str_replace('%code%',$sended['NewsletterSended']['code'],$newsletter['Newsletter']['html']);
		}elseif($admin){
			$newsletter['Newsletter']['html'] = str_replace('reenable/%code%/','reenable/newsletter_id:'.$newsletter['Newsletter']['id'].'/',$newsletter['Newsletter']['html']);
			$newsletter['Newsletter']['html'] = str_replace('decline/%sended_id%/','decline/newsletter_id:'.$newsletter['Newsletter']['id'].'/',$newsletter['Newsletter']['html']);
		}
	}
} 

?>