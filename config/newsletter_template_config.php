<?php
class NewsletterTemplateConfig extends Object {

	var $label = null;
	var $name = null;
	
	function getLabel(){
		return __(empty($this->label)?$this->name:$this->label,true);
	}
	
	
	
	function beforeConfig($data,$controller){ //deprecated
	}
	
	function beforeRender($data,$controller){
	
	}
	
	function beforeRenderEdit($data,$controller){
		return $this->beforeConfig($data,$controller);//deprecated
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