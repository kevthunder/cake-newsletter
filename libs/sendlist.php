<?php
class Sendlist extends Object {
	/*
		App::import('Lib', 'Newsletter.Sendlist');
	*/
	
	///////// Static Functions /////////
	function isTabled($sendlist_id){
		$tableSendlists = Configure::read('Newsletter.tableSendlist');
		if(!empty($tableSendlists)){
			foreach($tableSendlists as $key => $tableSendlist){
				if(isset($tableSendlist['id'])){
					if($tableSendlist['id'] == $sendlist_id){
						return true;
					}
				}elseif($key==$sendlist_id){
					return true;
				}
			}
		}
		return false;
	}
	
	function getSendlist($sendlist_id){
		if(Sendlist::isTabled($sendlist_id)){
			App::import('Lib', 'Newsletter.TabledSendlist');
			return new TabledSendlist($sendlist_id);
		}else{
			return new Sendlist($sendlist_id);
		}
	}
	
	function disable_email($email,$user_action = false){
		$emails = (array)$email;
	
		$NewsletterEmail = ClassRegistry::init('Newsletter.NewsletterEmail');
		$format = $NewsletterEmail->getDataSource()->columns['datetime']['format'];
	
		$email_data = array();
		$email_data['active'] = '0';
		$email_data['disabled'] = $NewsletterEmail->getDataSource()->value(date($format));
		if($user_action){
			$email_data['user_action'] = $NewsletterEmail->getDataSource()->value(date($format));
		}
		
		$NewsletterEmail->checkActive = false;
		$normalEmail = $NewsletterEmail->find('list',array('fields'=> array('id','email'),'conditions' => array('email'=>$emails),'recursive'=>-1));
		$count = $NewsletterEmail->updateAll($email_data, array('id'=>array_keys($normalEmail)));
		$normalCount = $NewsletterEmail->getAffectedRows();
		
		App::import('Lib', 'Newsletter.TabledSendlist');
		$tableSendlists = TabledSendlist::all();
		foreach($tableSendlists as $tableSendlist){
			if($tableSendlist->allowUnsubscribe()){
				$count += $tableSendlist->EmailModel->updateAll(array($tableSendlist->realField('active')=>0), array($tableSendlist->realField('email')=>$emails));
			}
		}
		
		$notInNormal = array_diff($emails, $normalEmail);
		foreach($notInNormal as $bkemail){
			//We keep unsubcriptions because tabled Sendlists are not allways reliable
			$data = $email_data;
			$data['email'] = $bkemail;
			$NewsletterEmail->create();
			if(!$NewsletterEmail->save($data)){
				return false;
			}
		}
		
		return $count;
	}
	
	function enable_email($email){
		$emails = (array)$email;
	
		$NewsletterEmail = ClassRegistry::init('Newsletter.NewsletterEmail');
		$format = $NewsletterEmail->getDataSource()->columns['datetime']['format'];
		
		$email_data = array();
		$email_data['active'] = '1';
		$email_data['user_action'] = $NewsletterEmail->getDataSource()->value(date($format));
		
		
		$count = $NewsletterEmail->updateAll($email_data, array('email'=>$emails));
		$normalCount = $NewsletterEmail->getAffectedRows();
		
		App::import('Lib', 'Newsletter.TabledSendlist');
		$tableSendlists = TabledSendlist::all();
		foreach($tableSendlists as $tableSendlist){
			if($tableSendlist->allowUnsubscribe()){
				$count += $tableSendlist->EmailModel->updateAll(array($tableSendlist->realField('active')=>1), array($tableSendlist->realField('email')=>$emails));
			}
		}
		
		return $count;
	}
	
	function addSendlistsEmailCond($sendlist,$opt=array(),$reset=true){
		$NewsletterSendlistsEmail = ClassRegistry::init('Newsletter.NewsletterSendlistsEmail');
		$NewsletterEmail = ClassRegistry::init('Newsletter.NewsletterEmail');
		$opt['joins'][] = array(
			'alias' => 'NewsletterSendlistsEmail',
			'table'=> $NewsletterSendlistsEmail->useTable,
			'type' => 'INNER',
			'conditions' => array(
				'NewsletterSendlistsEmail.newsletter_email_id = '.$NewsletterEmail->alias.'.id',
			)
		);
		$opt['conditions']['NewsletterSendlistsEmail.newsletter_sendlist_id'] = $sendlist;
		
		return $opt;
	}
	
	/////////  /////////
	var $id;
	var $EmailModel;
	var $type = 'default';
	
	function __construct($id){
		$this->id = $id;
		$this->EmailModel = ClassRegistry::init('Newsletter.NewsletterEmail');
	}
	
	function alterEmailQuery($opt=array(),$reset=true){
		$opt = Sendlist::addSendlistsEmailCond($this->id,$opt,$reset);
		
		if(!empty($opt['active'])){
			$opt['conditions'][$this->EmailModel->alias.'.active'] = 1;
		}
		
		return $opt;
	}
	
	function searchQuery($q,$opt=array()){
		$opt['conditions'][] = array('OR' => array(
			$this->EmailModel->alias . '.email LIKE' => '%'.$q.'%',
			$this->EmailModel->alias . '.name LIKE' => '%'.$q.'%'
		));
		return $opt;
	}
	
	function emailFields(){
		$fields = array();
		foreach($this->EmailModel->schema() as $key => $val){
			$fields[$key] = $this->EmailModel->alias.'.'.$key;
		}
		return $fields;
	}
	
	function emailQuery($opt=array(),$reset=true){
		$opt = $this->alterEmailQuery($opt,$reset);
		if(!empty($opt['search'])){
			$opt = $this->searchQuery($opt['search'],$opt);
		}
		unset($opt['search']);
		unset($opt['active']);
		return $opt;
	}
	function findEmail($mode,$opt=array()){
		if(is_array($mode)) {
			$opt = $mode;
		}elseif(!empty($mode)){
			$opt['mode'] = $mode;
		}
		if(empty($opt['mode'])){
			$opt['mode'] = 'plain';
		}
		$opt = $this->emailQuery($opt);
		//debug($opt);
		$res = $this->EmailModel->find(($opt['mode']=='plain')?'all':$opt['mode'],$opt);
		if($opt['mode'] == 'plain'){
			$res = parseResult($res);
		}
		return $res;
	}
	
	function parseResult($res,$options=array()){
		if(empty($res)) return $res;
		
		
		if(!is_array($options)){
			$options = array('alias'=>$options);
		}
		$defOpt = array(
			'alias'=> null,
		);
		$opt = array_merge($defOpt,$options);
		
		foreach($res as &$r){
			if(!empty($opt['alias'])){
				$r = array($opt['alias'] => $r[$this->EmailModel->alias]);
			}else{
				$r = $r[$this->EmailModel->alias];
			}
		}
		return $res;
	}
	
	function nbEmails(){
		return $this->findEmail('count',array('active'=>1));
	}
	
	function getInfo(){
		$NewsletterSendlist = ClassRegistry::init('Newsletter.NewsletterSendlist');
		return $NewsletterSendlist->find('first', array(
			'conditions'=>array(
				'id' => $this->id
			),
			'recursive' => -1
		));
	}
}
?>