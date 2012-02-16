<?php
class NewsletterBox extends NewsletterAppModel {

	var $name = 'NewsletterBox';
	//var $useTable = 'newsletter_boxes';
	
	var $multimedia = array(
		'multimedia' => array(
			'types' => array('photo'),
			'fields' => array()
		)
	);

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