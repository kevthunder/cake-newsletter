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
	
	function beforeSave($options) {
		if(!empty($this->data['NewsletterEmail']['sendlist_id'])){
			$this->data['NewsletterSendlist'] = (array)$this->data['NewsletterEmail']['sendlist_id'];
		}
		if(empty($this->data['NewsletterEmail']['id'])){
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
			//debug($exists);
			if($exists){
				$this->data['NewsletterEmail']['id'] = $exists['NewsletterEmail']['id'];
				$this->data['NewsletterEmail']['existed'] = 1;
				foreach($exists['NewsletterSendlistsEmail'] as $l){
					$this->data['NewsletterSendlist'][] = $l['newsletter_sendlist_id'];
				}
			}
		}
		//debug($this->data);
		//exit();
		return true;
	}
}
?>