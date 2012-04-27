<?php
class NewsletterBox extends NewsletterAppModel {

	var $name = 'NewsletterBox';
	//var $useTable = 'newsletter_boxes';
	
	var $multimedia = array(
		'multimedia' => array(
			'types' => array('photo'),
			'fields' => array()
		)
	);

	//The Associations below have been created with all possible keys, those that are not needed can be removed
	var $belongsTo = array(
		'Newsletter' => array(
			'className' => 'Newsletter.Newsletter',
			'foreignKey' => 'newsletter_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
	
	function beforeSave(){
		$box = $this->data["NewsletterBox"];
		if(isset($box["file"])){
			$box["data"]["file"] = $box["file"];
			unset($box["file"]);
		}
		if(!empty($box["data"])){
			if(!empty($box['data']['types'])){
				foreach($box['data']['types'] as $field => $type){
					if(method_exists($this,'_'.$type.'_before')){
						$res = $this->{'_'.$type.'_before'}($box['data'][$field],$box);
						if(!is_null($res)){
							$box['data'][$field] = $res;
						}
					}
				}
			}
			$box["data"] = serialize($box["data"]);
		}
		$this->data["NewsletterBox"] = $box;
		return true;
	}
	
	function afterFind($results, $primary){
		$doubleMulti = isset($results[0][$this->alias][0]);
		if($doubleMulti){
			$results = $results[0];
		}
		$test = $results;
		$rootnamed = isset($results[$this->alias]);
		if($rootnamed){
			$results = $results[$this->alias];
		}
		$multi = Set::numeric(array_keys($results));
		if(!$multi){
			$results = array($results);
		}
		foreach ($results as $key => $box) {
			$boxnamed = isset($box[$this->alias]);
			if($boxnamed){
				$box = $box[$this->alias];
			}
			if(isset($box["data"]) && empty($box["data"])){
				$box["data"] = array();
			}
			if(!empty($box["data"]) && !is_array($box["data"])){
				if(preg_match('/^{"/',$box["data"])){
					//old format
					$box["data"] = $this->json_dec($box["data"]);
				}else{
					$box["data"] = unserialize($box["data"]);
				}
				if(!empty($box['data']['types'])){
					foreach($box['data']['types'] as $field => $type){
						if(method_exists($this,'_'.$type.'_after')){
							$res = $this->{'_'.$type.'_after'}($box['data'][$field],$box);
							if(!is_null($res)){
								$box['data'][$field] = $res;
							}
						}
					}
				}
				if(isset($box["data"]["file"])){
					$box["file"] = $box["data"]["file"];
					unset($box["data"]["file"]);
				}
			}
			if($boxnamed){
				$box = array($this->alias=>$box);
			}
			$results[$key] = $box;
		}
		if(!$multi){
			$results = $results[0];
		}
		if($rootnamed){
			$results = array($this->alias=>$results);
		}
		if($doubleMulti){
			$results = array($results);
		}
		
		//debug($test);
		//debug($results);
		
		return $results;
	}
	
	
	
	function _tinymce_before($value,$box){
		
		if(Router::url('/') != '/'){
			//filter urls
			$findUrl = '/=["\']'.str_replace('/','\/',Router::url('/')).'([-\/_=?&%.:#a-zA-Z0-9]*)["\']/';
			//debug($findUrl);
			while(preg_match($findUrl,$value,$matches,PREG_OFFSET_CAPTURE)){
				$fullUrl = '/'.$matches[1][0];
				$value = substr($value,0,$matches[0][1]).'="'.$fullUrl.'"'.substr($value,$matches[0][1]+strlen($matches[0][0]));
				//debug($matches);
			}
		}
		
		return $value;
	}
	function _tinymce_after($value,$box){
		
		if(Router::url('/') != '/'){
			//filter urls
			$findUrl = '/=["\']\/(?!'.str_replace('/','\/',substr(Router::url('/'),1)).')([-\/_=?&%.:#a-zA-Z0-9]*)["\']/';
			//debug($findUrl);
			while(preg_match($findUrl,$value,$matches,PREG_OFFSET_CAPTURE)){
				$fullUrl = Router::url('/').$matches[1][0];
				$value = substr($value,0,$matches[0][1]).'="'.$fullUrl.'"'.substr($value,$matches[0][1]+strlen($matches[0][0]));
				//debug($matches);
			}
		}
		
		return $value;
	}
	
	
	
	function json_dec($value){
		return $this->recur_utf8_decode(json_decode($value,true));
	}
	function recur_utf8_decode($value){
		if(is_string($value)){
			$value = utf8_decode($value);
		}elseif(is_array($value)){
			foreach($value as &$val){
				$val = $this->recur_utf8_decode($val);
			}
		}
		return $value;
	}
	

}
?>