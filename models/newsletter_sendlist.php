<?php
class NewsletterSendlist extends NewsletterAppModel {

	var $name = 'NewsletterSendlist';
	
	var $hasMany = array(
		'NewsletterEmail' => array(
			'className' => 'Newsletter.NewsletterEmail',
			'foreignKey' => 'sendlist_id',
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
	
}
?>