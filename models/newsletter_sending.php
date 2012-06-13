<?php
class NewsletterSending extends NewsletterAppModel {
	var $name = 'NewsletterSending';
	var $displayField = 'date';
	
	var $actsAs = array('Newsletter.Serialized'=>array('selected_lists','data'));
	//The Associations below have been created with all possible keys, those that are not needed can be removed

	var $belongsTo = array(
		'Newsletter' => array(
			'className' => 'Newsletter.Newsletter',
			'foreignKey' => 'newsletter_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
	
	function notEmpty2($check){
		return !empty($check);
	}
	
	function beforeSave($created) {
		if(!empty($this->data[$this->alias]['additional_emails']) && is_array($this->data[$this->alias]['additional_emails'])){
			$emails = array();
			foreach ($this->data[$this->alias]['additional_emails'] as $email) {
				if(is_array($email)){
					if(empty($email['email'])){
						$email = false;
					}else{
						if(empty($email['name']) && (!empty($email['firstname']) || !empty($email['lastname'])) ){
							$email['name'] = $email['first_name'] .' '. $email['last_name'];
						}
						if(!empty($email['name'])){
							$email = $email['name'] . ' <'.$email['email'].'>';
						}else{
							$email = $email['email'];
						}
					}
				}
				if(!empty($email)){
					$emails[] = $email;
				}
			}
			$this->data[$this->alias]['additional_emails'] = implode(',',$emails);
			
		}
		//debug($this->data);
		return true;
	}
}
?>