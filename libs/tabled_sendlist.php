<?php
App::import('Lib', 'Newsletter.Sendlist');
class TabledSendlist extends Sendlist {
	/*
		App::import('Lib', 'Newsletter.TabledSendlist');
	*/
	
	///////// Static Functions /////////
	function all(){
		$tableSendlists = Configure::read('Newsletter.tableSendlist');
		if(!empty($tableSendlists)){
			$sendlists = array();
			foreach($tableSendlists as $key => $tableSendlist){
				$sendlists[$tableSendlist['id']] = new TabledSendlist($tableSendlist['id']);
			}
			return $sendlists;
		}
		return null;
	}
	function allIds(){
		$tableSendlists = Configure::read('Newsletter.tableSendlist');
		if(!empty($tableSendlists)){
			$sendlists = array();
			foreach($tableSendlists as $key => $tableSendlist){
				$sendlists[] = $tableSendlist['id'];
			}
			return $sendlists;
		}
		return null;
	}
	
	function getOptions($tableSendlist_id,$getModel = false){
		if(is_array($tableSendlist_id)){
			return $tableSendlist_id;
		}
		$tableSendlists = Configure::read('Newsletter.tableSendlist');
		if(!empty($tableSendlists)){
			foreach($tableSendlists as $key => $tableSendlist){
				if(isset($tableSendlist['id'])){
					if($tableSendlist['id'] == $tableSendlist_id){
						return TabledSendlist::parseOptions($tableSendlists[$key],$key,$getModel);
					}
				}elseif($key==$tableSendlist_id){
					return TabledSendlist::parseOptions($tableSendlists[$key],$key,$getModel);
				}
			}
		}
		return null;
	}
	
	function parseOptions($tableSendlist,$id = null,$getModel = false){
		if(!empty($tableSendlist)){
			$defaultOpt = array(
					'fields'=>array(
						'email'=>'email',
						'name'=>'name',
						'active'=>'active',
						'first_name'=>null,
						'last_name'=>null,
					),
					'showInnactive'=>true,
					'conditions'=>null,
					'allowUnsubscribe'=>true,
					'findOptions'=>null,
					'recursive'=>-1,
					'checkUnsubscribe'=>true,
				);
			if(!is_array($tableSendlist)){
				$tableSendlist = array('model'=>$tableSendlist);
			}
			if(isset($tableSendlist['model']) && $tableSendlist['model']){
				foreach($tmp = $tableSendlist as $key =>$val){
					if(preg_match('/^(\w+)field$/i',$key,$match)){
						$tableSendlist['fields'][Inflector::underscore($match[1])] = $val;
						unset($tableSendlist[$key]);
					}
				}
				if(!empty($tableSendlist['fields'])){
					foreach($tmp = $tableSendlist['fields'] as $key =>$val){
						if(is_numeric($key)){
							unset($tableSendlist['fields'][$key]);
							$tableSendlist['fields'][$val] = $val;
						}
					}
				}
				$tableSendlist = Set::merge($defaultOpt,$tableSendlist);
				//debug($tableSendlist);
				if(!isset($tableSendlist['id'])){
					if(!empty($id)){
						$tableSendlist['id'] = $id;
					}else{
						return null;
					}
				}
				if($getModel){
					$model = $tableSendlist['model'];
					//$modelName = $model;
					//if (strpos($model, '.') !== false) {
					//	list($plugin, $modelName) = explode('.', $model);
					//}
					//App::import('Model', $model);
					//$Model = new $modelName();
					$Model = ClassRegistry::init($model); 
					
					$tableSendlist['modelClass'] = $Model;
				}
				
				return $tableSendlist;
			}
		}
		return null;
	}
	
	/////////  /////////
	
	var $options;
	var $type = 'tabled';
	
	function __construct($id){
		$this->id = $id;
		$this->options = TabledSendlist::getOptions($id);
		$this->EmailModel = ClassRegistry::init($this->options['model']); 
	}
	
	function alterEmailQuery($opt){
		$Model = $this->EmailModel;
		$modelName = $Model->alias;
		
		if(!empty($opt)){
			$NewsletterEmail = ClassRegistry::init('Newsletter.NewsletterEmail'); 
			App::import('Lib', 'Newsletter.SetMulti');
			$replace = array(
					$NewsletterEmail->alias.'.email' => $this->realField('email'),
					$NewsletterEmail->alias => $modelName
				);
			$opt = SetMulti::replaceTree(array_keys($replace),array_values($replace),$opt);
		}
	
	
		$conditions = array();
		if(	
				!empty($opt['active']) 
				|| (!isset($opt['active']) && !$this->options['showInnactive'])
		){
			if($opt['mode'] != 'count' && $this->options['checkUnsubscribe']){
				$NewsletterEmail = ClassRegistry::init('Newsletter.NewsletterEmail');
				$opt['joins'][] = array(
					'alias' => $NewsletterEmail->alias,
					'table'=> $NewsletterEmail->useTable,
					'type' => 'LEFT',
					'conditions' => array(
						$this->realField('email').' = '.$NewsletterEmail->alias.'.email'
					)
				);
				$conditions[] = array('or'=>array(
					$NewsletterEmail->alias.'.active' => 1,
					$NewsletterEmail->alias.'.id IS NULL'
				));
			}
			if(!empty($this->options['fields']['active']) && $Model->hasField($this->options['fields']['active'])){
				$conditions[$this->realField('active')] = 1;
			}
		}
		$conditions['NOT'][$this->realField('email')] = "";
		$conditions[] = $this->realField('email').' IS NOT NULL';
		if(!empty($this->options['conditions'])){
			if(!array($this->options['conditions'])){
				$this->options['conditions'] = array($this->options['conditions']);
			}
			$conditions = set::merge($conditions,$this->options['conditions']);
		}
		$opt['conditions'][] = $conditions;
		
		if((empty($opt['mode']) || $opt['mode'] != 'count') && empty($opt['fields'])){
			$opt['fields'] = $this->emailFields();
		}
		if(!empty($this->options['findOptions']) && is_array($this->options['findOptions'])){
			$opt = set::merge($opt,$this->options['findOptions']);
		}
		
		return $opt;
	}
	
	
	function searchQuery($q,$opt=array()){
		$fields = $this->emailFields(array('exclude'=>array('id','primary_key','active')));
		$cond = array();
		foreach($fields as $f){
			$schema = $this->EmailModel->schema(end(explode('.',$f)));
			if($schema['type'] != 'boolean'){
				$cond['OR'][$f.' LIKE'] = '%'.$q.'%';
			}
		}
		$opt['conditions'][] = $cond;
		return $opt;
	}
	function emailFields($opt=array()){
		$modelName = $this->EmailModel->alias;
		$dbo = $this->EmailModel->getDataSource();
		$fields = array();
		$fields['id'] = $modelName.'.'.$this->EmailModel->primaryKey;
		foreach($this->options['fields'] as $alias => $field){
			if(!empty($field) && $this->EmailModel->hasField($field)){
				$fields[$alias] = $modelName.'.'.$field;
			}
		}
		$fields['primary_key'] = $modelName.'.'.$this->EmailModel->primaryKey;
		//$fields[] = '*';
		if(!empty($opt['exclude'])){
			$fields = array_diff_key($fields,array_flip($opt['exclude']));
		}
		return $fields;
	}
	
	function realField($alias){
		if(!empty($this->options['fields'][$alias])){
			return $this->EmailModel->alias . '.' . $this->options['fields'][$alias];
		}
		return null;
	}
	
	function allowUnsubscribe(){
		return $this->options['allowUnsubscribe'] && $this->EmailModel->hasField($this->options['fields']['active']);
	}
	
	function parseResult($res,$options=array()){
		if(empty($res)) return $res;
		
		if(!is_array($options)){
			$options = array('alias'=>$options);
		}
		$defOpt = array(
			'alias'=> null,
			'local'=>false,
		);
		$opt = array_merge($defOpt,$options);
		
		
		$Model = $this->EmailModel;
		$modelName = $Model->alias;
		$single = isset($res[$modelName]);
		$fields = $this->options['fields'];
		//debug($fields);
		if($single) $res = array($res);
		
		$emails = array();
		foreach ($res as $pos => &$mail) {
			$emailData = array();
			if(isset($mail[$modelName]['email'])){
				$emailData['email']= $mail[$modelName]['email'];
			}else if($fields['email'] && isset($mail[$modelName][$fields['email']])){
				$emailData['email']= $mail[$modelName][$fields['email']];
			}
			
			if(!empty($emailData['email'])){
				$emails[$pos] = $emailData['email'];
				//$basicFields = array('id','email','name','first_name','last_name');
				//$emailData = array_intersect_key($mail[$modelName],array_flip($basicFields));
				//if(array_key_exists($Model->primaryKey, $mail[$modelName]) {}
				$emailData['id'] = $mail[$modelName][$Model->primaryKey];
				//debug($mail[$modelName]);
				if($fields['active'] && array_key_exists($fields['active'],$mail[$modelName])){
					$emailData['active']= $mail[$modelName][$fields['active']];
				}else if(array_key_exists('active',$mail[$modelName])){
					$emailData['active']= $mail[$modelName]['active'];
				}else{
					$emailData['active']= 1;
				}
				$name = array();
				if(isset($mail[$modelName]['first_name']) && $mail[$modelName]['first_name']){
					$name[] = $mail[$modelName]['first_name'];
				}else if($fields['first_name'] && isset($mail[$modelName][$fields['first_name']]) && $mail[$modelName][$fields['first_name']]){
					$name[] = $mail[$modelName][$fields['first_name']];
				}
				if(isset($mail[$modelName]['last_name']) && $mail[$modelName]['last_name']){
					$name[] = $mail[$modelName]['last_name'];
				}else if($fields['last_name'] && isset($mail[$modelName][$fields['last_name']]) && $mail[$modelName][$fields['last_name']]){
					$name[] = $mail[$modelName][$fields['last_name']];
				}
				$name = implode(' ',$name);
				if(!$name){
					if(isset($mail[$modelName]['name'])){
						$name = $mail[$modelName]['name'];
					}else if($fields['name'] && isset($mail[$modelName][$fields['name']])){
						$name = $mail[$modelName][$fields['name']];	
					}
				}
				$emailData['name'] = $name;
				$emailData['sendlist_id'] = $this->id;
				$emailData['data'] = $mail;
				if(!empty($opt['alias'])){
					$mail = array($opt['alias'] => $emailData);
				}else{
					$mail = $emailData;
				}
			}
		}
		if($opt['local'] && ! empty($emails)){
			$localModel = ClassRegistry::init('Newsletter.NewsletterEmail');
			$locals = $localModel->find('all', array('conditions'=>array('email'=>$emails),'recursive'=>-1));
			foreach($locals as $local){
				$res[array_search($local['NewsletterEmail']['email'],$emails)]['local'] = $local['NewsletterEmail'];
			}
		}
		if($single) $res = $res[0];
		return $res;
	}
	
}
?>