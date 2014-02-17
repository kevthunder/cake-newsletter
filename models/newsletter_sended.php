<?php
class NewsletterSended extends NewsletterAppModel {

	var $name = 'NewsletterSended';
	var $useTable = 'newsletter_sended';

	//The Associations below have been created with all possible keys, those that are not needed can be removed
	var $belongsTo = array(
		'Newsletter' => array(
			'className' => 'Newsletter.Newsletter',
			'foreignKey' => 'newsletter_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'NewsletterEmail' => array(
			'className' => 'Newsletter.NewsletterEmail',
			'foreignKey' => 'email_id',
			'conditions' => array(
				'NewsletterSended.tabledlist_id IS NULL'
			),
			'fields' => '',
			'order' => ''
		),
		'NewsletterSending' => array(
			'className' => 'Newsletter.NewsletterSending',
			'foreignKey' => 'sending_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'NewsletterVariant' => array(
			'className' => 'Newsletter.NewsletterVariant',
			'foreignKey' => 'newsletter_variant_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
	
	var $hasMany = array(
		'NewsletterEvent' => array(
			'className' => 'Newsletter.NewsletterEvent',
			'foreignKey' => 'sended_id',
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