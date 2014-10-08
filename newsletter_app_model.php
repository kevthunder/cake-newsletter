<?php
class NewsletterAppModel extends AppModel {
	
	function getNextAutoIncrement(){

        $next_increment = 0;
		
		if($this->useTable){
			$table = $this->useTable;
		}else{
        	$table = Inflector::tableize($this->name);
		}

        $query = "SHOW TABLE STATUS LIKE '$table'";

        $db =& ConnectionManager::getDataSource($this->useDbConfig);

        $result = $db->rawQuery($query);


		
        while ($row = mysql_fetch_assoc($result)) {
			
            $next_increment = $row['Auto_increment'];

        }



        return $next_increment;

    } 
	
	function unbindModelAll($reset = true) { 
		$unbind = array(); 
		foreach ($this->belongsTo as $model=>$info) { 
		  $unbind['belongsTo'][] = $model; 
		} 
		foreach ($this->hasOne as $model=>$info) { 
		  $unbind['hasOne'][] = $model; 
		} 
		foreach ($this->hasMany as $model=>$info) { 
		  $unbind['hasMany'][] = $model; 
		} 
		foreach ($this->hasAndBelongsToMany as $model=>$info) { 
		  $unbind['hasAndBelongsToMany'][] = $model; 
		} 
		parent::unbindModel($unbind,$reset); 
	} 
	
	function createMany($fields = null, $values = null) {
	
		$id = null;

		if (empty($fields)) {
			return false;
		}
		
		
		$db =& ConnectionManager::getDataSource($this->useDbConfig);
		
		$fields = $this->tcheckSaveFields($fields);
		$empty = array();
		foreach($fields as $field){
			$empty[$field] = null;
		}
		$count = count($fields);
		
		
		foreach($values as $vals){
			$vals = array_values(array_merge($empty,array_intersect_key($vals,$empty)));
			for ($i = 0; $i < $count; $i++) {
				$vals[$i] = $db->value($vals[$i], $this->getColumnType($fields[$i]), false);
			}
			$vals = implode(', ', $vals);
			$valueInsert[] = $vals;
		}
		
		
		for ($i = 0; $i < $count; $i++) {
			$fieldInsert[] = $db->name($fields[$i]);
			if ($fields[$i] == $this->primaryKey) {
				$id = $values[$i];
			}
		}
		$query = array(
			'table' => $db->fullTableName($this),
			'fields' => implode(', ', $fieldInsert),
			'values' => implode('),( ', $valueInsert)
		);
		
		//debug($db->renderStatement('create', $query));
		if ($db->execute($db->renderStatement('create', $query))) {
			/*if (empty($id)) {
				$id = $db->lastInsertId($db->fullTableName($this, false), $this->primaryKey);
			}
			$this->setInsertID($id);
			$this->id = $id;*/
			return true;
		} else {
			$this->onError();
			return false;
		}
	}
	
	function tcheckSaveFields($fields = array()){
		$valid = array();
		foreach($fields as $field){
			if ($this->hasField($field) && (empty($this->whitelist) || in_array($field, $this->whitelist))) {
				$valid[] = $field;
			}
		}
		return $valid;
	}
	
	
	function afterFind($results,$primary){
		$results = parent::afterFind($results,$primary);
		
		if(!$primary){
			$return = $this->behaviorsTrigger($this, 'assocAfterFind', array($results, $primary), array('modParams' => true));
			if ($return !== true) {
				$results = $return;
			}
		}
		
		return $results;
	}
	
	
	function behaviorsTrigger(&$model, $callback, $params = array(), $options = array()) {
		
		if (empty($this->Behaviors->_attached)) {
			return true;
		}
		$options = array_merge(array('break' => false, 'breakOn' => array(null, false), 'modParams' => false), $options);
		$count = count($this->Behaviors->_attached);

		for ($i = 0; $i < $count; $i++) {
			$name = $this->Behaviors->_attached[$i];
			if (in_array($name, $this->Behaviors->_disabled)) {
				continue;
			}
			if(method_exists($this->Behaviors->{$name},$callback)){
				$result = $this->Behaviors->{$name}->dispatchMethod($model, $callback, $params);
			}else{
				$result = false;
			}

			if ($options['break'] && ($result === $options['breakOn'] || (is_array($options['breakOn']) && in_array($result, $options['breakOn'], true)))) {
				return $result;
			} elseif ($options['modParams'] && is_array($result)) {
				$params[0] = $result;
			}
		}
		if ($options['modParams'] && isset($params[0])) {
			return $params[0];
		}
		return true;
	}

}
?>