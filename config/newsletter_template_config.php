<?php
class NewsletterTemplateConfig extends Object {

	var $label = null;
	var $name = null;
	
	function getLabel(){
		return __(empty($this->label)?$this->name:$this->label,true);
	}
	
	
	
	function beforeConfig(&$model){
	
	}
	
	function beforeRender(&$model){
	
	}
	
	
	function beforeFind(&$model, $queryData){
	}
	
	function afterFind(&$model, $results,  $primary){
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