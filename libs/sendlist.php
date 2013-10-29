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
	
	/////////  /////////
	var $id;
	var $EmailModel;
	
	function __construct($id){
		$this->id = $id;
		$this->EmailModel = ClassRegistry::init('Newsletter.NewsletterEmail');
	}
	
	function alterEmailQuery($opt){
		return $opt;
	}
	
	function emailFields(){
		$fields = array();
		foreach($this->EmailModel->schema() as $key => $val){
			$fields[$key] = $this->EmailModel->alias.'.'.$key;
		}
		return $fields;
	}
	
	function findEmail($mode,$opt=array()){
		if(is_array($mode)) {
			$opt = $mode;
		}elseif(!empty($mode)){
			$opt['mode'] = $mode;
		}
		$opt = $this->alterEmailQuery($opt);
		//debug($opt);
		$res = $this->EmailModel->find($opt['mode'],$opt);
		foreach($res as &$r){
			$r = $r[$this->EmailModel->alias];
		}
		return $res;
	}
}
?>