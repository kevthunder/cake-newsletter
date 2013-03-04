<?php
class PhpMailNewsletterSender extends NewsletterSender {
	
	function send($opt){
	
		if(empty($opt['email']) || empty($opt['content'])){
			return false;
		}
		
		//// options ////
		$this->Email = $this->controller->Email;
		$this->Email->reset();
		$smtpOptions = Configure::read('Newsletter.smtpOptions');
		if(!empty($smtpOptions)){
			//debug($smtpOptions);
			$this->Email->smtpOptions = $smtpOptions;
			$this->Email->delivery = 'smtp';
		}
		$this->Email->lineLength = 1000;
		$this->Email->sendAs = 'html';
		
		//// get recipient (to) ////
		$this->Email->to = $opt['to'];
		$this->Email->subject = $opt['subject'];
		
		//// get sender (from) ////
		$this->Email->from = $opt['from'];
		
		//// get replyTo ////
		if(!empty($opt['replyTo'])){
			$this->Email->replyTo = $opt['replyTo'];
		}
		
		//// get errorReturn ////
		if(!empty($opt['return'])){
			$this->Email->return = $opt['return'];
		}
		
		//// Replace content placeholders ////
		$cur_content = $opt['content'];
		if(!empty($opt['replace'])){
			$cur_content = str_replace(array_keys($opt['replace']),array_values($opt['replace']),$cur_content);
		}
		
		//// Send ////
		return $this->Email->send($cur_content);
	}
}