<?php
class NewsletterEmail extends NewsletterAppModel {

	var $name = 'NewsletterEmail';

	var $belongsTo = array(
		'NewsletterSendlist' => array(
			'className' => 'Newsletter.NewsletterSendlist',
			'foreignKey' => 'sendlist_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
}
?>