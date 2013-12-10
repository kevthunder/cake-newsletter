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
			$findOptions['fields'] = $this->aliasedFields($findOptions['fields']);
		}
		return $findOptions;
	}
}