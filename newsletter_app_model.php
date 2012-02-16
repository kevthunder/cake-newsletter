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

}
?>