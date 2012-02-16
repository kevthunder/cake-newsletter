<?php
class NewsletterSending extends NewsletterAppModel {
	var $name = 'NewsletterSending';
	var $displayField = 'date';
	
	var $actsAs = array('Newsletter.Serialized'=>array('selected_lists'));
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
}
?>