<?php
class NewsletterUpgradeController extends NewsletterAppController {

	var $name = 'NewsletterUpgrade';
	var $helpers = array('Html', 'Form');
	var $components = array();
	var $uses = array();

	function admin_upgrade(){
		clearCache(null, 'models');
		$sErrors = NewsletterUpgrade::check();
		if(!$sErrors){
			$this->Session->setFlash(__d('newsletter','The database is valid', true));
			$this->redirect(array('plugin'=>'newsletter','controller'=>'newsletter','action'=>'index'));
		}
		//debug($sErrors);
		if(!empty($this->params['named']['start'])){
			$error = array();
			$step = empty($this->params['named']['step'])?1:$this->params['named']['step'];
			$res = NewsletterUpgrade::run($error);
			if($res === 'break'){
				$this->redirect(array('start'=>'1','step'=>$step++));
			}
			if($res === true){
				$this->Session->setFlash(__d('newsletter','The database has been fixed', true));
				$this->redirect(array('plugin'=>'newsletter','controller'=>'newsletter','action'=>'index'));
			}else{
				$this->Session->setFlash(
					__d('newsletter','An error occurred', true)
					.' :<ul><li>'
					.implode('</li><li>',$error)
					.'</li></ul>'
				);
			}
			/*
			App::import('Lib', 'Newsletter.QueryUtil'); 
			if(!empty($sErrors['missing_table'])){
				$this->_fix_missing_tables($sErrors,$error);
			}elseif(!empty($sErrors['field_mismatch'])){
				if(in_array('NewsletterEmail.sendlist_id',$sErrors['field_mismatch']['fields'])){
					$this->_fix_email_list_relation($sErrors,$error);
				}
				if(in_array('NewsletterSended.sendlist_id',$sErrors['field_mismatch']['fields'])){
					$this->_fix_sended_tabled($sErrors,$error);
				}
				if(in_array('NewsletterSended.newsletter_variant_id',$sErrors['field_mismatch']['fields'])){
					$this->_fix_sended_variant($sErrors,$error);
				}
				if(in_array('NewsletterSended.name',$sErrors['field_mismatch']['fields'])){
					$this->_fix_sended_name($sErrors,$error);
				}
				clearCache(null, 'models');
			}
			if(!empty($error)){
				$this->Session->setFlash(
					__d('newsletter','An error occurred', true)
					.' :<ul><li>'
					.implode('</li><li>',$error)
					.'</li></ul>'
				);
			}else{
				$this->Session->setFlash(__d('newsletter','The database has been fixed', true));
				$this->redirect(array('plugin'=>'newsletter','controller'=>'newsletter','action'=>'index'));
			}
			*/
		}
	}
	
	
	function _fix_missing_tables($sErrors,&$error){
		$db =& ConnectionManager::getDataSource('default');
			
		$sqlFile = App::pluginPath('Newsletter').'database.sql';
		$sql = file_get_contents($sqlFile);
		$sql = preg_replace('/^-.*/m','',$sql);
		//$sql = preg_replace('/.$/','lol2',$sql);
		if($sql){
			//debug($matches);
			foreach(explode(';',$sql) as $q){
				$q = trim($q);
				if(strpos($q,'CREATE TABLE') === 0 && !$db->execute($q)){
					$error[] = __('Could not create tables : ',true).$q;
					break;
				}
			}
		}else{
			$error[] = __('`newsletter_sendlists_emails` create statement not found.',true);
		}
		if(empty($error)){
			$this->Session->setFlash(__d('newsletter','The database has been fixed', true));
			$this->redirect(array('start'=>'1','step'=>2));
		}
	}
	
	function _fix_email_list_relation($sErrors,&$error){
		$db =& ConnectionManager::getDataSource('default');
		
		$this->NewsletterSendlistsEmail = ClassRegistry::init('Newsletter.NewsletterSendlistsEmail');
		$this->NewsletterEmail = ClassRegistry::init('Newsletter.NewsletterEmail');
		$queries = array();
		
		$findOpt = array(
			'fields' => array('NewsletterEmail.id','NewsletterEmail.id'),
			'conditions'=>array(),
			'group'=>'`email` HAVING COUNT(`id`) > 1',
			'order'=>'COUNT(`id`) DESC',
			//'limit' => 50,
			'recursive' => -1,
		);
		$duplicated = $this->NewsletterEmail->find('list',$findOpt);
		if(!empty($duplicated)){
			debug($duplicated);
			
			$findOpt = array(
				'fields' => array('NewsletterEmail.id', 'e2.sendlist_id'),
				'conditions'=>array(
					'NewsletterEmail.id' => $duplicated,
				),
				'joins' => array(
					array(
						'alias' => 'e2',
						'table'=> $this->NewsletterEmail->useTable,
						'type' => 'INNER',
						'conditions' => array(
							'`e2`.`sendlist_id` != `NewsletterEmail`.`sendlist_id`',
							'`e2`.`email` = `NewsletterEmail`.`email`'
						)
					)
				),
				'group'=> array('NewsletterEmail.id', 'e2.sendlist_id'),
				//'order'=>"`NewsletterEmail`.`email` like '%kgiguere%' DESC",
				//'limit' => 50,
				'recursive' => -1,
				'model' => $this->NewsletterEmail,
			);
			
			$query = $db->buildStatement(QueryUtil::standardizeFindOptions($findOpt),$findOpt['model']);
			$insertStatement = 'INSERT INTO '.$this->NewsletterSendlistsEmail->useTable.' (`newsletter_email_id`,`newsletter_sendlist_id`) ('.$query.')';
			$queries[] = $insertStatement;
			
			$findOpt = array(
				'fields' => array('e2.*'),
				'conditions'=>array(
					'NewsletterEmail.id' => $duplicated,
				),
				'joins' => array(
					array(
						'alias' => 'e2',
						'table'=> $this->NewsletterEmail->useTable,
						'type' => 'INNER',
						'conditions' => array(
							'`e2`.`id` != `NewsletterEmail`.`id`',
							'`e2`.`email` = `NewsletterEmail`.`email`'
						)
					)
				),
				//'group'=> array('NewsletterEmail.id', 'e2.sendlist_id'),
				//'order'=>"`NewsletterEmail`.`email` like '%kgiguere%' DESC",
				//'limit' => 50,
				'recursive' => -1,
				'model' => $this->NewsletterEmail,
			);
			App::import('Lib', 'Newsletter.QueryUtil'); 
			$query = $db->buildStatement(QueryUtil::standardizeFindOptions($findOpt),$findOpt['model']);
			$queries[] = 'DELETE'.substr($query,6);
		}
		
		$findOpt = array(
			'fields' => array('NewsletterEmail.id','NewsletterEmail.sendlist_id'),
			'conditions'=>array('NewsletterEmail.sendlist_id IS NOT NULL'),
			//'limit' => 50,
			'recursive' => -1,
			'model' => $this->NewsletterEmail,
		);
		$query = $db->buildStatement(QueryUtil::standardizeFindOptions($findOpt),$findOpt['model']);
		$insertStatement = 'INSERT INTO '.$this->NewsletterSendlistsEmail->useTable.' (`newsletter_email_id`,`newsletter_sendlist_id`) ('.$query.')';
		$queries[] = $insertStatement;
		
		$queries[] = 'ALTER TABLE `'.$this->NewsletterEmail->useTable.'` DROP `sendlist_id`;';
		
		debug($queries);
		foreach($queries as $query){
			if(!$db->execute($query)){
				$error[] = __('Unable to execute query :',true).' '.$query;
				break;
			}
		}
	}
	
	
	function _fix_sended_tabled($sErrors,&$error){
		$db =& ConnectionManager::getDataSource('default');
		
		$NewsletterSended = ClassRegistry::init('Newsletter.NewsletterSended');
		$query = 'ALTER TABLE `'.$NewsletterSended->useTable.'` CHANGE `sendlist_id` `tabledlist_id` INT( 11 ) NULL DEFAULT NULL ;';
		debug($query);
		if(!$db->execute($query)){
			$error[] = __('Unable to execute query :',true).' '.$query;
		}
	}
	
	function _fix_sended_variant($sErrors,&$error){
		$db =& ConnectionManager::getDataSource('default');
		
		$NewsletterSended = ClassRegistry::init('Newsletter.NewsletterSended');
		$query = 'ALTER TABLE `'.$NewsletterSended->useTable.'` ADD  `newsletter_variant_id` INT NULL AFTER  `newsletter_id` ;';
		debug($query);
		if(!$db->execute($query)){
			$error[] = __('Unable to execute query :',true).' '.$query;
		}
	}
	
	function _fix_sended_name($sErrors,&$error){
		$db =& ConnectionManager::getDataSource('default');
		
		$NewsletterSended = ClassRegistry::init('Newsletter.NewsletterSended');
		$query = 'ALTER TABLE `'.$NewsletterSended->useTable.'` ADD `name` VARCHAR( 255 ) NULL AFTER  `email` ;';
		debug($query);
		if(!$db->execute($query)){
			$error[] = __('Unable to execute query :',true).' '.$query;
		}
	}

}
?>