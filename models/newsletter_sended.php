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
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'NewsletterSendlist' => array(
			'className' => 'Newsletter.NewsletterSendlist',
			'foreignKey' => 'sendlist_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'NewsletterSending' => array(
			'className' => 'Newsletter.NewsletterSending',
			'foreignKey' => 'sending_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
	
	var $hasMany = array(
		'NewsletterStat' => array(
			'className' => 'Newsletter.NewsletterStat',
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