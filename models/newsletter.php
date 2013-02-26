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
		),
		'NewsletterAssoc' => array(
			'className' => 'Newsletter.NewsletterAssoc',
			'foreignKey' => 'my_newsletter_id',
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
	
	/*var $hasAndBelongsToMany = array(
		'AssociatedNewsletter' => array(
			'with' => 'Newsletter.NewsletterAssoc'
			'className' => 'Newsletter.Newsletter',
			'foreignKey' => 'my_newsletter_id',
			'associationForeignKey' => 'newsletter_id',
			'unique' => true,
		)
	);*/
	
	
	function minFields($rel = false){
		$fields = array();
		$excludeFields = array('html');
		$schema = $this->schema();
		foreach($schema as $field => $opt){
			if(!in_array($field,$excludeFields)){
				$fields[] = $this->alias.'.'.$field;
			}
		}
		if($rel){
		}
		return $fields;
	}
	
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
	
	/*function beforeSave($options) {
		return true;
	}*/
	
	function afterSave($created) {
		//debug($this->data);
		//debug($this->data['Newsletter']['associated']);
		if(array_key_exists('associated',$this->data['Newsletter'])){
			$this->NewsletterAssoc->recursive = -1;
			$this->NewsletterAssoc->deleteAll(array('or'=>array('my_newsletter_id' => $this->id,'newsletter_id' => $this->id)));
			if(!empty($this->data['Newsletter']['associated'])){
				foreach((array)$this->data['Newsletter']['associated'] as $lang => $assocId){
					//debug($assocId);
					if(!empty($assocId) && (empty($this->data['Newsletter']['lang']) || $lang != $this->data['Newsletter']['lang'])){
						$this->NewsletterAssoc->create();
						$this->NewsletterAssoc->save(array('type'=>$lang, 'my_newsletter_id' => $this->id, 'newsletter_id' => $assocId));
						$this->NewsletterAssoc->create();
						$this->NewsletterAssoc->save(array('type'=>$this->data['Newsletter']['lang'], 'my_newsletter_id' => $assocId, 'newsletter_id' => $this->id));
					}
				}
			}
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
			
			if(!empty($resRoot['NewsletterAssoc'])){
				foreach($resRoot['NewsletterAssoc'] as $assoc){
					$res['associated'][$assoc['type']] = $assoc['newsletter_id'];
				}
			}
		}
		return $results;
	}
	
	

}
?>