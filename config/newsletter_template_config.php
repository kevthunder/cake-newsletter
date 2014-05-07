<?php
class NewsletterTemplateConfig extends Object {

	var $label = null;
	var $name = null;
	
	function getLabel(){
		return __(empty($this->label)?$this->name:$this->label,true);
	}
	
	function getPath(){
		if(empty($this->path)){
			$this->path = null;
			$paths = NewsletterConfig::getAllViewPaths();
			foreach($paths as $path) {
				$file = $path.'/elements/newsletter/'.Inflector::underscore($this->name).'.ctp';
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

}
?>