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
	
	function minFields($rel = true){
		$fields = array();
		$excludeFields = array('html','console');
		$schema = $this->schema();
		foreach($schema as $field => $opt){
			if(!in_array($field,$excludeFields)){
				$fields[] = $this->alias.'.'.$field;
			}
		}
		if($rel){
			$fields = array_merge($fields, $this->Newsletter->minFields());
		}
		return $fields;
	}
	
	function getPendingCond($raw = false){
		if($raw){
			return  '`'.$this->alias.'`.`started` IS NOT NULL AND '.
					'`'.$this->alias.'`.`active` = 1 AND '.
					'`'.$this->alias.'`.`confirm` = 1 AND '.
					'`'.$this->alias."`.`status` <> 'done'";
		}else{
			return array(
				$this->alias.'.started IS NOT NULL',
				$this->alias.'.active' => 1,
				$this->alias.'.confirm' => 1,
				$this->alias.'.status NOT' => 'done',
			);
		}
	}
	
	function getScheduledCond($raw = false){
		if($raw){
			return  '`'.$this->alias.'`.`scheduled` = 1 AND '.
					'`'.$this->alias.'`.`active` = 1  AND '.
					'`'.$this->alias.'`.`confirm` = 1';
		}else{
			return array(
				$this->alias.'.scheduled' => 1,
				$this->alias.'.active' => 1,
				$this->alias.'.confirm' => 1,
			);
		}
	}
	
	function cancel($id, $recuperable = true){
		$data = array('active'=>0);
		if(!$recuperable){
			$data['status'] = "'cancelled'";
		}
		$this->unbindModelAll();
		$this->updateAll($data, array($this->alias.'.id' => $id));
		if(!$recuperable){
			$this->NewsletterSended = ClassRegistry::init('NewsletterSended');
			$data = array(
				'active' => 0,
				'status' => "'cancelled'"
			);
			$this->NewsletterSended->unbindModelAll();
			$this->NewsletterSended->updateAll($data, array(
				$this->NewsletterSended->alias.'.status' => 'ready',
				$this->NewsletterSended->alias.'.sending_id' => $id,
			));
		}
	}
	

	
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
		if(!empty($this->data[$this->alias]['selected_lists'])){
			$this->data[$this->alias]['selected_lists'] = $this->unserializeFunct($this->data[$this->alias]['selected_lists']);
			$this->data[$this->alias]['selected_lists'] = array_values($this->data[$this->alias]['selected_lists']);
			$this->data[$this->alias]['selected_lists'] = $this->serializeFunct($this->data[$this->alias]['selected_lists']);
		}
		//debug($this->data);
		return true;
	}
}
?>