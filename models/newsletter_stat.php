<?php
class NewsletterStat extends NewsletterAppModel {

	var $name = 'NewsletterStat';

	//The Associations below have been created with all possible keys, those that are not needed can be removed
	var $belongsTo = array(
		'NewsletterSended' => array(
			'className' => 'Newsletter.NewsletterSended',
			'foreignKey' => 'sended_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

}
?>