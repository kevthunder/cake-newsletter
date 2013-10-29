<?php
App::import('Lib', 'Sendlist');
class TabledSendlist extends Sendlist {
	/*
		App::import('Lib', 'Newsletter.TabledSendlist');
	*/
	
	///////// Static Functions /////////
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
						'firstName'=>null,
						'lastName'=>null,
					),
					'showInnactive'=>true,
					'conditions'=>null,
					'allowUnsubscribe'=>true,
					'findOptions'=>null,
					'recursive'=>-1
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
	
	function __construct($id){
		$this->id = $id;
		$this->options = TabledSendlist::getOptions($id);
		$this->EmailModel = ClassRegistry::init($this->options['model']); 
	}
	
	function emailFields(){
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
		return $fields;
	}
	
}
?>