<?php
class NewsletterSendlistsEmail extends NewsletterAppModel {
	var $name = 'NewsletterSendlistsEmail';
	//The Associations below have been created with all possible keys, those that are not needed can be removed

	var $belongsTo = array(
		'NewsletterSendlist' => array(
			'className' => 'NewsletterSendlist',
			'foreignKey' => 'newsletter_sendlist_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'NewsletterEmail' => array(
			'className' => 'NewsletterEmail',
			'foreignKey' => 'newsletter_email_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
}
?>