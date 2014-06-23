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
	
	function bindCode($ids=null,$len = 16){
		if(empty($ids)) $ids = $this->id;
		$ids = (array)$ids;
		$codes = $this->newCode(count($ids),$len);
		$res = array();
		foreach(array_values($ids) as $i => $id){
			$this->create();
			if($this->save(array(
				'id' => $id,
				'code' => $codes[$i],
			))){
				$res[$id] = $codes[$i];
			}
		}
		return $res;
	}
	
	function newCode($nb = 1,$len = 16){
		$codes = array();
		$chars = "abcdefghijklmnopqrstuvwxyz1234567890";
		for ($i = 0; $i < $nb; $i++) {
			$code = '';
			for ($j = 0; $j < $len; $j++) {
				$code .= $chars[rand(0,strlen($chars)-1)];
			}
			$codes[] = $code;
		}
		$existants = $this->find('list',array('fields'=>array('id','code'),'conditions'=>array('code'=>$codes),'recursive'=>-1));
		if(!empty($existants)){
			$codes = array_diff($codes,$existants);
			$codes = array_merge($codes,$this->newCode(count($existants),$len));
		}
		return $codes;
	}
	
	function byCode($code,$opt=array()){
		App::import('Lib', 'Newsletter.QueryUtil'); 
		$findOpt = QueryUtil::mergeFindOpt(array('recursive'=>-1),$opt,array('conditions'=>array($this->alias.'.code'=>$code)));
		return $this->find('first',$findOpt);
	}

}
?>