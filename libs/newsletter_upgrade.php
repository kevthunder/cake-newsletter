<?php
class NewsletterUpgrade extends Object {
	/*
		App::import('Lib', 'Newsletter.NewsletterUpgrade');
	*/
	var $connection = 'default';
	var $tests = array(
		'movedStats' => array(
			//'break' => true,
			'recheck' => true,
		),
		'schemaAdd' => array(
			//'break' => true,
			'recheck' => true,
		),
		'emailListRelation' => array(
			'recheck' => true,
		),
		'sendedTabled' => array(
			'recheck' => true,
		),
		'schemaUpdate' => array(
			'dropConstraint' => array(
				'newsletter_emails' => array(
					'only' => array('sendlist_id')
				)
			),
			'recheck' => true,
		),
		'htmlLongText' => array(
			'fields'=> array(
				'newsletters' => 'html',
				'newsletter_variants' => 'html',
				'newsletter_sendings' => 'html',
			)
		),
	);
	var $defOpt = array(
		'check'=>null,
		'fix'=>null,
		'recheck' => false,
		'break' => false,
	);
	
	//$_this =& NewsletterUpgrade::getInstance();
	function &getInstance() {
		static $instance = array();
		if (!$instance) {
			$instance[0] =& new NewsletterUpgrade();
		}
		return $instance[0];
	}
	
	
	function check(){
		$_this =& NewsletterUpgrade::getInstance();
		
		$cached = Cache::read('newsletter_upgraded');
		if( Configure::read('debug') < 1 && $cached){//heavyCache
			return false;
		}
		
		$schemaSha1 = $_this->getSchemaSha1();
		if($schemaSha1 == $cached){
			return false;
		}
		
		$_this->errors = array();
		foreach($_this->tests as $test => &$opt){
			$opt = array_merge($_this->defOpt,$opt);
			if(!isset($opt['check'])){
				if(method_exists('NewsletterUpgrade','check_'.$test)){
					$opt['check'] = 'check_'.$test;
				}else{
					$opt['check'] = false;
				}
			}
			if($opt['check']){
				$res = $_this->{$opt['check']}($opt);
				if($res) {
					$_this->errors[$test] = $res;
					if($opt['recheck']){
						break;
					}
				}
			}
		}
		if(!empty($_this->errors)){
			return $_this->errors;
		}
		$_this->_clearCaches();
		
		Cache::write('newsletter_upgraded', $schemaSha1?$schemaSha1:1);
		
		return false;
	}
	
	function run(&$msgs){
		$_this =& NewsletterUpgrade::getInstance();
		
		$db =& ConnectionManager::getDataSource($_this->connection);
		$db->cacheSources = false;
		
		$_this->check();
		while(!empty($_this->errors)){
			foreach($_this->errors as $test => $error){
				$opt = &$_this->tests[$test];
				if(!isset($opt['fix'])){
					if(method_exists('NewsletterUpgrade','fix_'.$test)){
						$opt['fix'] = 'fix_'.$test;
					}else{
						$opt['fix'] = false;
					}
				}
				if($opt['fix']){
					$msg = array();
					$res = $_this->{$opt['fix']}($error,$opt,$msg);
					if(!$res){
						if(empty($msg)) $msg = '"'.$test.'" fix failled';
						$msgs = array_merge($msgs,(array)$msg);
						return false;
					}
					if($opt['break']){
						$_this->_clearCaches();
						return 'break';
					}
					if($opt['recheck']){
						$_this->_clearCaches();
						$_this->check();
						continue 2;
					}
				}
			}
			$_this->errors = array();
		}
		
		
		return true;
	}
	
	///////////////////////////////////////////////////////////////////////
	/////////////////////////// util functions ///////////////////////////
	///////////////////////////////////////////////////////////////////////
	
	function getSchema(){
		$_this =& NewsletterUpgrade::getInstance();
		if(empty($_this->Schema)){
			App::import('Model', 'CakeSchema', false);
			$_this->Schema =& new CakeSchema(array('name'=>'Newsletter', 'file'=>'schema.php', 'connection'=>$_this->connection, 'plugin'=>'Newsletter'));
			$_this->Schema = $_this->Schema->load();
		}
		return $_this->Schema;
	}
	
	function getSchemaSha1(){
		$_this =& NewsletterUpgrade::getInstance();

		App::import('Model', 'CakeSchema', false);
		$Schema =& new CakeSchema(array('name'=>'Newsletter', 'file'=>'schema.php', 'connection'=>$_this->connection, 'plugin'=>'Newsletter'));
		$file = $Schema->path . DS . $Schema->file;
		
		return sha1(sha1_file($file).serialize($this->tests));
	}
	
	function getSourceList(){
		$_this =& NewsletterUpgrade::getInstance();
		if(empty($_this->sourceList)){
			App::import('Lib', 'ConnectionManager');
			$db =& ConnectionManager::getDataSource($_this->connection);
			
			$_this->sourceList = $db->listSources();
		}
		return $_this->sourceList;
	}
	
	function getSchemaDiff(){
		$_this =& NewsletterUpgrade::getInstance();
		if(empty($_this->schemaDiff)){
			$Schema = $_this->getSchema();
			$SchemaCurrent =& new CakeSchema(array('name'=>'Newsletter', 'file'=>'schema.php', 'connection'=>$_this->connection, 'plugin'=>'Newsletter'));
			$SchemaCurrent =$_this->Schema->read(array('models'=>false));
			//debug($Schema);
			//debug($SchemaCurrent);
			$_this->schemaDiff = $Schema->compare($SchemaCurrent);
		}
		return $_this->schemaDiff;
	}
	
	function _clearCaches(){
		clearCache(null, 'models');
		ClassRegistry::flush( );
		$this->schemaDiff = null;
		$this->sourceList = null;
	}
	///////////////////////////////////////////////////////////////////////
	/////////////////////////// CHECK functions ///////////////////////////
	///////////////////////////////////////////////////////////////////////
	
	function check_movedStats(){
		$_this =& NewsletterUpgrade::getInstance();
		
		$sourceList = $_this->getSourceList();
		if(in_array('newsletter_stats',$sourceList) && !in_array('newsletter_events',$sourceList)){
			return true;
		}
		return false;
	}
	
	function check_schemaAdd(){
		$_this =& NewsletterUpgrade::getInstance();
		
		$sourceList = $_this->getSourceList();
		$Schema = $_this->getSchema();
		$tables = array_diff(array_keys($Schema->tables),$sourceList);
		if(!empty($tables)) return $tables;
		
		return false;
	}
	function check_emailListRelation($opt){
		$_this =& NewsletterUpgrade::getInstance();
		$diff = $_this->getSchemaDiff();
		return !empty($diff['newsletter_emails']['drop']['sendlist_id']);
	}
	function check_sendedTabled($opt){
		$_this =& NewsletterUpgrade::getInstance();
		$diff = $_this->getSchemaDiff();
		return !empty($diff['newsletter_sended']['drop']['sendlist_id']);
	}
	function check_schemaUpdate($opt){
		$_this =& NewsletterUpgrade::getInstance();
		
		$diff = $_this->getSchemaDiff();
		
		if(!empty($diff)){
			$edit = array();
			foreach($diff as $table => $modif){
				if(!empty($modif['drop'])){
					if(!empty($opt['dropConstraint'][$table]['only'])){
						$modif['drop'] = array_intersect_key($modif['drop'],array_flip((array)$opt['dropConstraint'][$table]['only']));
					}
					if(empty($modif['drop'])) unset($modif['drop']);
				}
				if(!empty($modif)) $edit[$table] = $modif;
			}
			if(!empty($edit)){
				return $edit;
			}
		}
		return false;
	}
	function check_htmlLongText($opt){
		$_this =& NewsletterUpgrade::getInstance();
		
		$db =& ConnectionManager::getDataSource($_this->connection);
		
		$mismatch = array();
		foreach ($opt['fields'] as $table=>$field) {
			$cols = $db->query('DESCRIBE `'.$table.'`');
			
			$htmlField = null;
			foreach($cols as $col){
				$colKey = key($col);
				if($col[$colKey]['Field'] == $field){
					$htmlField = $col[$colKey];
					break;
				}
			}
			if($htmlField['Type'] != 'longtext'){
				$mismatch[$table] = $field;
			}
		}
		
		if(!empty($mismatch)) return $mismatch;
		return false;
	}
	/////////////////////////////////////////////////////////////////////
	/////////////////////////// FIX functions ///////////////////////////
	/////////////////////////////////////////////////////////////////////
	function fix_movedStats($error,$opt,&$msg){
		$_this =& NewsletterUpgrade::getInstance();
		
		$db =& ConnectionManager::getDataSource($_this->connection);
		$query = $db->createSchema($_this->getSchema(),'newsletter_events');
		//debug($query);
		if(!$db->execute($query)){
			$msg = str_replace('%table%','newsletter_events',__('Failled to create table `%table%` :',true)).' '.$query;
			return false;
		}
		
		$query = 'INSERT INTO  `newsletter_events` (`id`,`sended_id`,`date`,`action`,`url`,`ip_address`,`user_agent`) SELECT `id`,`sended_id`,`date`,`action`,`url`,`ip_address`,`user_agent` FROM `newsletter_stats` ;';
		//debug($query);
		if(!$db->execute($query)){
			$msg = str_replace(array('%table1%','%table2%'),array('newsletter_stats','newsletter_events'),__('Failled to copy `%table1%` into `%table2%` :',true)).' '.$query;
			return false;
		}
		
		$query = 'DROP TABLE `newsletter_stats` ;';
		//debug($query);
		if(!$db->execute($query)){
			$msg = str_replace('%table%','newsletter_stats',__('Failled delete old `%table%` table :',true)).' '.$query;
			return false;
		}

	
		return true;
	}
	
	function fix_schemaAdd($error,$opt,&$msg){
		$_this =& NewsletterUpgrade::getInstance();
		
		$Schema = $_this->getSchema();
		$db = ConnectionManager::getDataSource($_this->connection);
		foreach($error as $table){
			$query = $db->createSchema($Schema,$table);
			//debug($query);
			if(!$db->execute($query)){
				$msg = str_replace('%table%',$table,__('Failled to create table `%table%` :',true)).' '.$query;
				return false;
			}
		}
		return true;
	}
	
	function fix_emailListRelation($error,$opt,&$msg){
		$_this =& NewsletterUpgrade::getInstance();
		$db = ConnectionManager::getDataSource($_this->connection);
		
		App::import('Lib', 'Newsletter.QueryUtil'); 
			
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
			//debug($duplicated);
			
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
		
		foreach($queries as $query){
			if(!$db->execute($query)){
				$msg[] = __('Unable to execute query :',true).' '.$query;
				return false;
				break;
			}
		}
	
		return true;
	}
	
	function fix_sendedTabled($error,$opt,&$msg){
		$_this =& NewsletterUpgrade::getInstance();
		$db =& ConnectionManager::getDataSource($_this->connection);
		
		$NewsletterSended = ClassRegistry::init('Newsletter.NewsletterSended');
		$query = 'ALTER TABLE `'.$NewsletterSended->useTable.'` CHANGE `sendlist_id` `tabledlist_id` INT( 11 ) NULL DEFAULT NULL ;';
		//debug($query);
		if(!$db->execute($query)){
			$msg = __('Unable to execute query :',true).' '.$query;
			return false;
		}
		return true;
	}
	
	function fix_schemaUpdate($error,$opt,&$msg){
		$_this =& NewsletterUpgrade::getInstance();
		$db =& ConnectionManager::getDataSource($_this->connection);
		$queries = array();
		
		foreach($error as $table => $modif){
			$queries[] = $db->alterSchema(array($table => $modif),$table);
		}
		
		//debug($queries);
		foreach($queries as $query){
			if(!$db->execute($query)){
				$msg[] = __('Unable to execute query :',true).' '.$query;
				return false;
				break;
			}
		}
		
		return true;
	}
	function fix_htmlLongText($error,$opt,&$msg){
		$_this =& NewsletterUpgrade::getInstance();
		$db =& ConnectionManager::getDataSource($_this->connection);
		
		foreach ($error as $table=>$field) {
			$query = 'ALTER TABLE  `'.$table.'` CHANGE  `'.$field.'`  `'.$field.'` LONGTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;';
			//debug($query);
			if(!$db->execute($query)){
				$msg = __('Unable to execute query :',true).' '.$query;
				return false;
			}
		}
		return true;
	}
}
?>