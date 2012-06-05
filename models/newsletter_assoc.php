<?php
class NewsletterAssoc extends NewsletterAppModel {
	var $name = 'NewsletterAssoc';
	//The Associations below have been created with all possible keys, those that are not needed can be removed

	var $belongsTo = array(
		'MyNewsletter' => array(
			'className' => 'Newsletter.Newsletter',
			'foreignKey' => 'my_newsletter_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
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