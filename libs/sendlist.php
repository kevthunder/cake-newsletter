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
	
	function parseResult($res,$useAlias=null){
		if(empty($res)) return $res;
		
		foreach($res as &$r){
			if($useAlias){
				$r = array($useAlias => $r[$this->EmailModel->alias]);
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
		return $NewsletterSendlist->read(null, $this->id);
	}
}
?>