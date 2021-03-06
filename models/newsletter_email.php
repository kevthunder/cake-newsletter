<?php
class NewsletterEmail extends NewsletterAppModel {

	var $name = 'NewsletterEmail';
	var $validate = array(
		'email' => array(
			'email' => array(
				'rule' => array('email'),
				//'message' => 'Your custom message here',
				'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);
	
	var $hasAndBelongsToMany = array(
		'NewsletterSendlist' => array(
			'with' => 'Newsletter.NewsletterSendlistsEmail',
			'className' => 'Newsletter.NewsletterSendlist',
			'foreignKey' => 'newsletter_email_id',
			'associationForeignKey' => 'newsletter_sendlist_id',
			'unique' => true,
		)
	);
	
	function save($data = null, $validate = true, $fieldList = array()) {
		$this->set($data);
		$this->parseSave();
		return parent::save(null, $validate, $fieldList);
	}
	
	function parseSave(){
		if(!empty($this->data['NewsletterEmail']['sendlist_id'])){
			$this->data['NewsletterSendlist'] = (array)$this->data['NewsletterEmail']['sendlist_id'];
		}
		
		if(empty($this->data['NewsletterEmail']['id'])){
			if(!array_key_exists('active',$this->data['NewsletterEmail'])){
				$this->data['NewsletterEmail']['active'] = 1;
			}
			$this->bindModel(array(
				'hasMany' => array(
					'NewsletterSendlistsEmail' => array(
						'className' => 'Newsletter.NewsletterSendlistsEmail'
					)
				)
			));
			$this->Behaviors->attach('Containable');
			$exists = $this->find('first', array(
				'fields'=>array('id','active'),
				'conditions'=>array(
					'email'=>$this->data['NewsletterEmail']['email'],
				),
				'contain'=>array(
					'NewsletterSendlistsEmail'=>array(
						'fields' => array('id','newsletter_sendlist_id'),
					)
				),
			));
			if($exists){
				$this->id = $exists['NewsletterEmail']['id'];
				$this->data['NewsletterEmail'] = array_merge($this->data['NewsletterEmail'],$exists['NewsletterEmail']);
				$this->data['NewsletterEmail']['existed'] = 1;
				foreach($exists['NewsletterSendlistsEmail'] as $l){
					$this->data['NewsletterSendlist'][] = $l['newsletter_sendlist_id'];
				}
			}
		}
	}
	
}
?>