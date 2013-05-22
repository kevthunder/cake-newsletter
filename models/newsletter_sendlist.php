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
	
	function move($nb,$entry = null){
		if(is_null($entry)){
			$entry = $this->id;
		}
		if(is_numeric($entry)){
			$entry = $this->find('first',array('fields'=>array('id','order'),'conditions'=>array($this->primaryKey => $entry),'recursive'=>-1));
		}
		if(!empty($entry[$this->alias]['id'])){
			$curPos = (int)$entry[$this->alias]['order'];
			$same = $this->find('count',array(
				'conditions'=>array(
					'IFNULL(`'.$this->alias.'`.`order`,0) = '.$curPos,
					$this->alias.'.id NOT' => $entry[$this->alias]['id'],
				),
				'recursive'=>-1
			));
			if($same){
				//If some entries have the same weight, split group
				$cond = array();
				if($nb<0){
					$cond = array(
						'IFNULL(`'.$this->alias.'`.`order`,0) >='.$curPos,
						$this->alias.'.id NOT' => $entry[$this->alias]['id'],
					);
					$nb++;
				}else{
					$cond = array('or'=>array(
						'IFNULL(`'.$this->alias.'`.`order`,0) >'.$curPos,
						$this->alias.'.id' => $entry[$this->alias]['id'],
					));
					$nb--;
					$curPos++;
				}
				$this->updateAll(
					array(
						$this->alias.'.order' => 'IFNULL(`'.$this->alias.'`.`order`,0) +1'
					),
					$cond
				);
			}
			if($nb != 0){
				$newPos = $curPos + $nb;
				$this->updateAll(
					array(
						$this->alias.'.order' => 'IFNULL(`'.$this->alias.'`.`order`,0) '.($nb<0?'+1':'-1')
					),
					array(
						'IFNULL(`'.$this->alias.'`.`order`,0) '.($nb>0?'>':'<').$curPos,
						'IFNULL(`'.$this->alias.'`.`order`,0) '.($nb>0?'<=':'>=').$newPos,
					)
				);
				$this->save(array(
					'id' =>$entry[$this->alias]['id'],
					'order'=>$newPos
				));
			}
			
		}
	}
	
}
?>