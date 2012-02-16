<?php
class Newsletter extends NewsletterAppModel {

	var $name = 'Newsletter';

	//The Associations below have been created with all possible keys, those that are not needed can be removed
	var $hasMany = array(
		'NewsletterBox' => array(
			'className' => 'Newsletter.NewsletterBox',
			'foreignKey' => 'newsletter_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'NewsletterSending' => array(
			'className' => 'Newsletter.NewsletterSending',
			'foreignKey' => 'newsletter_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		)
	);
	
	function beforeConfig(){
		if(isset($this->data[$this->alias]['TemplateConfig'])){
			$this->data[$this->alias]['TemplateConfig']->beforeConfig($this);
		}
	}
	
	function beforeRender(){
		if(isset($this->data[$this->alias]['TemplateConfig'])){
			$this->data[$this->alias]['TemplateConfig']->beforeRender($this);
		}
	}
	
	
	function afterFind($results,  $primary){
		if(!Set::numeric(array_keys($results))){
			$tmp = array(&$results);
			$myResults =& $tmp;
		}else{
			$myResults =& $results;
		}
		foreach($myResults as &$resRoot){
			////// get updated Data //////
			if(isset($resRoot[$this->alias])){
				$res =& $resRoot[$this->alias];
			}else{
				$res =& $resRoot;
			}
			
			if(!empty($res['template'])){
				$res['TemplateConfig'] = ClassCollection::getObject('NewsletterConfig',$res['template']);
			}
		}
		return $results;
	}
	
	

}
?>