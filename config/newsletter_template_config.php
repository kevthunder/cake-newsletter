<?php
class NewsletterTemplateConfig extends Object {

	var $label = null;
	var $name = null;
	var $element_folder = 'newsletter';
	
	function getLabel(){
		return __(empty($this->label)?$this->name:$this->label,true);
	}
	
	function getPath(){
		if(empty($this->path)){
			$this->path = null;
			$paths = NewsletterConfig::getAllViewPaths();
			foreach($paths as $path) {
				$file = $path.'elements' . DS . $this->element_folder . DS .Inflector::underscore($this->name).'.ctp';
				if(file_exists($file)){
					$this->path = $file;
					break;
				}
			}
		}
		return $this->path;
	}
	
	function check(){
		$path = $this->getPath();
		if(!empty($path)){
			$contents = file_get_contents($path);
			if(strpos($contents,'$newsletter->') != false){
				return false;
			}
		}
		return true;
	}
	
	function beforeConfig($data,$controller){ //deprecated
	}
	
	function beforeRender($data,$controller){
	
	}
	
	function beforeRenderEdit($data,$controller){
		return $this->beforeConfig($data,$controller);//deprecated
	}
	
	function form($view){
		
	}
	
	function getGroupOpts($newsletter,$sendlist){
		return null;
	}
	
	function getGrouping($newsletter,$sendlist = null){
		if(is_numeric($newsletter)){
			$NewsletterSending = ClassRegistry::init('Newsletter.NewsletterSending');
			$newsletter = $NewsletterSending->read(null,$newsletter);
		}
		$opt = $this->getGroupOpts($newsletter,$sendlist);
		if(is_null($opt)){
			return null;
		}
		$defOpt = array(
			'fields' => array(),
			'validation' => array(),
			'withMissing' => array(
				'disable' => false,
				'validate' => true,
			),
			'bySendlist'=>false,
		);
		if(!count(array_intersect_key($opt,$defOpt))){
			$opt = array('fields'=>$opt);
		}
		return Set::merge($defOpt,$opt);
	}
	
	function getDefaultSendlists($newsletter){
		return null;
	}
	
	function beforeSend($sender,&$opt,&$mailsOptions){
	
	}
	
	function afterSend($sender,$opt,$mailsOptions){
	}
	
	function beforeView(&$newsletter,$sended,$admin){
	
	}
	
	function afterFind(&$model, $result){
	}
	
	function beforeValidate(&$model){
	}
	
	function beforeSave(&$model, $options){
	}
	
	function afterSave(&$model, $created){
	}
	
	
	function beforeDelete(&$model, $cascade){
	}
	
	function afterDelete(&$model){
	}
	
	////// utility funct //////
	function beforeSendCreateCodes(&$mailsOptions,$opt,$len=16){
		if(!empty($opt['sending']['NewsletterSending']['status']) && $opt['sending']['NewsletterSending']['status'] == 'test'){
		}else{
			$this->NewsletterSended = ClassRegistry::init('Newsletter.NewsletterSended');
			
			$to_bind = array();
			foreach($mailsOptions as $sended_id => $sopt){
				if(empty($sopt['email']['code'])){
					$to_bind[] = $sended_id;
				}
			}
			
			$binded = $this->NewsletterSended->bindCode($to_bind,$len);
			foreach($binded as $sended_id => $code){
				$mailsOptions[$sended_id]['email']['code'] = $code;
				$mailsOptions[$sended_id]['replace']['%code%'] = $code;
			}
		}
	}

}
?>