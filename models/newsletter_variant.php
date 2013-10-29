<?php
class NewsletterVariant extends NewsletterAppModel {
	var $name = 'NewsletterVariant';
	//The Associations below have been created with all possible keys, those that are not needed can be removed

	var $actsAs = array('Newsletter.Serialized'=>array('conditions'));
	
	var $belongsTo = array(
		'Newsletter' => array(
			'className' => 'Newsletter',
			'foreignKey' => 'newsletter_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
}
?>