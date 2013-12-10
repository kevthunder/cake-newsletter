<?php
class QueryUtil extends Object {

	//App::import('Lib', 'Newsletter.QueryUtil'); 

	
	function standardizeFindOptions($findOptions){
		$empty=array(
			'order' => array(),
			'limit' => null,
			'group' => array()
		);
		$findOptions = array_merge($empty,$findOptions);
		if(!empty($findOptions['model'])){
			$findOptions['table']=$findOptions['model']->useTable;
			$findOptions['alias']=$findOptions['model']->alias;
		}
		unset($findOptions['model']);
		if(!empty($findOptions['fields'])){
			$findOptions['fields'] = QueryUtil::aliasedFields($findOptions['fields']);
		}
		return $findOptions;
	}
	
	function aliasedFields($fields){
		$formated = array();
		foreach($fields as $alias => $field){
			if(!is_numeric($alias)){
				//À Faire : $db->name(
				$field .= ' as `'.$alias.'`';
			}
			$formated[] = $field;
		}
		return $formated;
	}
}