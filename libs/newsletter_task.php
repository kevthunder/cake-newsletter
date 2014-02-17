<?php
class NewsletterTask extends Object {
	/*
		App::import('Lib', 'Newsletter.NewsletterTask');
	*/

	function sync($async=false){
		/*debug($async);
		if($async){
			@ob_flush();
			flush();
			//session_write_close();
			ignore_user_abort(true);
			set_time_limit(0);
		}*/
		NewsletterTask::syncSender();
		NewsletterTask::updateBounces();
		//debug("sync");
	}
	
	function syncSender(){
		$Newsletter = ClassRegistry::init('Newsletter.Newsletter');
		
		App::import('Lib', 'Newsletter.NewsletterConfig');
		$newsletterSyncTimout = NewsletterConfig::load('newsletterSyncTimout');
	
		//// init sender class ////
		App::import('Lib', 'Newsletter.ClassCollection');
		$senderOpt = NewsletterConfig::load('sender');
		if(!is_array($senderOpt)){
			$senderOpt = array('name' => $senderOpt);
		}
		$sender = ClassCollection::getObject('NewsletterSender',$senderOpt['name']);
		
		
		if(method_exists($sender,'sync')){
				
			$findOpt = array(
				'fields'=>array(
					'Newsletter.id',
					'Newsletter.title',
					'Newsletter.last_sync',
					'Newsletter.date',
					'Newsletter.external_key',
					'NewsletterSending.last_process_time',
					'NewsletterSending.status',
				),
				'conditions'=>array(
				),
				'joins' => array(
					array(
						'alias' => $Newsletter->NewsletterSending->alias,
						'table'=> $Newsletter->NewsletterSending->useTable,
						'type' => 'INNER',
						'conditions' => array(
							'NewsletterSending.newsletter_id = Newsletter.id',
							'NewsletterSending.confirm' => 1,
							'NewsletterSending.active' => 1,
							'NewsletterSending.status' => array('done','send'),
							'or' => array(
								'Newsletter.last_sync IS NULL',
								'Newsletter.last_sync < DATE_ADD(NewsletterSending.last_process_time, INTERVAL '.$newsletterSyncTimout.')',
							)
						)
					)
				),
				'limit'=>'10',
				'group'=>'Newsletter.id',
				'order'=>'Newsletter.date DESC',
				'recursive'=>-1,
			);
			$toSync = $Newsletter->find('all',$findOpt);
			if(!empty($toSync)){
				$ids = array();
				foreach($toSync as $n){
					$ids[] = $n['Newsletter']['id'];
				}
				
				if($sender->sync($toSync)){
					$format = $Newsletter->getDataSource()->columns['datetime']['format'];
					$Newsletter->updateAll(array('last_sync'=>"'".date($format)."'"), array('Newsletter.id' => $ids));
				}
			}
		}
	}
	
	function updateBounces(){
		App::import('Lib', 'Newsletter.NewsletterConfig');
		$bounceLimit = NewsletterConfig::load('bounceLimit');
		$NewsletterEvent = ClassRegistry::init('Newsletter.NewsletterEvent');
		$findOpt = array(
			'fields'=>array(
				'count(NewsletterSended.id) as count',
				'count(NewsletterSended.id)>='.$bounceLimit.' as to_drop',
				'NewsletterEvent.*',
				'NewsletterSended.email_id',
				'NewsletterSended.tabledlist_id',
				'NewsletterSended.email',
			),
			'conditions'=>array(
				'NewsletterEvent.action' => 'bounce',
				'NewsletterEvent.processed' => 0,
				'or'=>array(
					'NewsletterSended.tabledlist_id IS NOT NULL',
					'NewsletterEmail.active'=>1,
				)
			),
			'joins' => array(
				array(
					'alias' => $NewsletterEvent->NewsletterSended->alias,
					'table'=> $NewsletterEvent->NewsletterSended->useTable,
					'type' => 'LEFT',
					'conditions' => array(
						'NewsletterSended.id = NewsletterEvent.sended_id',
					)
				),
				array(
					'alias' => 'OtherEvent',
					'table'=> $NewsletterEvent->useTable,
					'type' => 'LEFT',
					'conditions' => array(
						'OtherEvent.sended_id = NewsletterSended.id',
					)
				),
				array(
					'alias' => $NewsletterEvent->NewsletterSended->NewsletterEmail->alias,
					'table'=> $NewsletterEvent->NewsletterSended->NewsletterEmail->useTable,
					'type' => 'LEFT',
					'conditions' => array(
						'NewsletterEmail.id = NewsletterSended.email_id',
						'NewsletterSended.tabledlist_id IS NULL'
					)
				)
			),
			'group' => 'NewsletterSended.id',
			'order' => array('count(NewsletterSended.id) DESC','NewsletterEvent.date DESC'),
			'recursive'=>-1,
			'limit'=>100,
		);
		$data = $NewsletterEvent->find('all',$findOpt);
		//debug($data[0]);
		$proccessed = array();
		$drops = array();
		foreach($data as $r){
			$proccessed[] = $r['NewsletterEvent']['id'];
			if($r[0]['to_drop']){
				$drops[$r['NewsletterSended']['tabledlist_id']?$r['NewsletterSended']['tabledlist_id']:0][$r['NewsletterSended']['email_id']] = true;
			}
		}
		//debug($drops);
		foreach($drops as $list => $ids){
			$Model = $activeField = $idField = null;
			if($list){
				App::import('Lib', 'Newsletter.Sendlist');
				$list = Sendlist::getSendlist($list);
				$fields = $list->emailFields();
				//debug($fields);
				if(!empty($fields['active'])){
					$Model = $NewsletterEvent->NewsletterSended->NewsletterEmail;
					$activeField = $fields['active'];
					$idField = $fields['id'];
				}
			}else{
				$Model = $NewsletterEvent->NewsletterSended->NewsletterEmail;
				$activeField = 'NewsletterEmail.active';
				$idField = 'NewsletterEmail.id';
			}
			if(!empty($Model)){
				$Model->updateAll(array($activeField=>0), array($idField => array_keys($ids)));
			}
		}
		$NewsletterEvent->updateAll(array('processed'=>1), array('NewsletterEvent.id' => $proccessed));
	}
}
?>