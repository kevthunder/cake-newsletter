<?php
class NewsletterStat extends NewsletterAppModel {
	var $name = 'NewsletterStat';
	var $displayField = 'name';
	//The Associations below have been created with all possible keys, those that are not needed can be removed

	var $belongsTo = array(
		'Newsletter' => array(
			'className' => 'Newsletter',
			'foreignKey' => 'newsletter_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
	
	function getStats($newsletter_id,$keys=null){
		$findOpt = array(
			'conditions'=>array('newsletter_id'=>$newsletter_id),
			'order'=>array('date DESC','created DESC'),
			'recursive'=>-1
		);
		if(isset($keys)) $findOpt['conditions']['name'] = $keys;
		$statsRaw = $this->find('all',$findOpt);
		$categorizedStats = array();
		foreach($statsRaw as $stat){
			$categorizedStats[$stat['NewsletterStat']['name']][] = $stat['NewsletterStat'];
		}
		return $categorizedStats;
	}
}
?>