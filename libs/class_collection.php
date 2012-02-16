<?php
class ClassCollection extends Object {

	var $types = array(
		'NewsletterConfig'=>array(
			'classSufix'=>'NewsletterConf',
			'fileSufix'=>null,
			'paths'=>'%app%/config/plugins/newsletter/',
			'ext'=>'php',
			'defaultByParent'=> true,
			'setName'=> true,
			'parent'=>array(
				'paths'=>'%app%/config/',
				'name'=>'Newsletter.NewsletterTemplateConfig'
			)
		),
		'NewsletterBoxConfig'=>array(
			'classSufix'=>'NewsletterBoxConf',
			'fileSufix'=>null,
			'paths'=>'%app%/config/plugins/newsletter_box/',
			'ext'=>'php',
			'defaultByParent'=> true,
			'setName'=> true,
			'parent'=>array(
				'paths'=>'%app%/config/',
				'name'=>'Newsletter.NewsletterTemplateConfig'
			)
		),
	);
	var $defaultOptions = array(
		'plugin'=>null,
		'classSufix'=>null,
		'fileSufix'=>null,
		'paths'=>'%app%/libs/',
		'ext'=>'php',
		'parent'=>null,
		'defaultByParent'=>false,
		'throwException'=>true,
		'setName'=>false,
	);
	var $parentInerit = array(
		'ext','paths'
	);
	
	//$_this =& ClassCollection::getInstance();
	function &getInstance() {
		static $instance = array();
		if (!$instance) {
			$instance[0] =& new ClassCollection();
		}
		return $instance[0];
	}
	
	function parseClassName($options){
		$name = $options['name'];
		if(!empty($options['classSufix'])){
			$name .= $options['classSufix'];
		}
		return $name;
	}
	
	function setType($type,$options){
		if(!isset($this->types[$type])){
			$this->types[$type] = array();
		}
		$this->types[$type] = Set::Merge($this->types[$type],$options);
	}
	
	function parseImportOption($options){
		$_this =& ClassCollection::getInstance();
		$options = Set::Merge($_this->defaultOptions,$options);
		if(strpos($options['name'],'.') !== false){
			list($options['plugin'],$options['name']) = explode('.',$options['name'],2);
		}
		$importOpt = array(
			'type'=>null,
			'name'=>$_this->parseClassName($options),
			'file'=>Inflector::underscore($options['name'])
		);
		if(!empty($options['fileSufix'])){
			$importOpt['file'] .= $options['fileSufix'];
		}
		$importOpt['file'] .= '.'.$options['ext'];
		if(!empty($options['paths'])){
			$paths = array();
			$appPath = APP;
			if(!empty($options['plugin'])){
				$appPath = App::pluginPath( $options['plugin'] );
			}
			foreach((array)$options['paths'] as $path){
				$path = str_replace('%app%',$appPath,$path);
				$path = str_replace('/',DS,$path);
				$path = str_replace(DS.DS,DS,$path);
				$paths[] = $path;
			}
			$importOpt['search'] = $paths;
		}
		//debug($importOpt);
		
		return $importOpt;
	}
	
	function getOption($type,$name){
		$_this =& ClassCollection::getInstance();
		$options = array();
		if(is_array($name)){
			$options = $name;
		}else{
			$options['name'] = $name;
		}
		
		if(!empty($_this->types[$type])){
			$options = Set::Merge($_this->types[$type],$options);
		}
		$options = Set::Merge($_this->defaultOptions,$options);
		
		if(is_null($options['plugin']) && strpos($options['name'],'.') !== false){
			list($options['plugin'],$options['name']) = explode('.',$options['name'],2);
		}
		
		$options['name'] = Inflector::camelize($options['name']);
		
		return $options;
	}
	
	function getObject($type,$name){
		$_this =& ClassCollection::getInstance();
		
		$options = $_this->getOption($type,$name);
		
		$class = $_this->parseClassName($options);
		$exitent = ClassRegistry::getObject($class);
		if($exitent){
			return $exitent;
		}
		$isParent = false;
		$class = $_this->getClass($type,$options,$isParent);
		//debug($class);
		if(!empty($class) && class_exists($class) ) {
			$created = new $class();
			if($options['setName'] && empty($created->name)){
				$created->name = $options['name'];
			}
			if($created && !$isParent){
				ClassRegistry::addObject($class, $created);
			}
			return $created;
		}
		return null;
	}
	
	
	function getClass($type,$name,&$isParent = false){
		$_this =& ClassCollection::getInstance();
		$options = $_this->getOption($type,$name);
		
		if(!empty($options['parent'])){
			$inerit = array_intersect_key($options,array_flip($_this->parentInerit));
			$parentOpt = Set::Merge($inerit,$options['parent']);
			$parent = $_this->getClass(null,$parentOpt);
			if(empty($parent)){
				return null;
			}
		};
		
		$importOpt = $_this->parseImportOption($options);
		
		if(App::import($importOpt)){
			return $importOpt['name'];
		}else{
			if(!empty($parent) && $options['defaultByParent']){
				$isParent = true;
				return $parent;
			}
			if($options['throwException']){
				debug($importOpt['name']. ' not found.');
			}
			return null;
		}
		
	}
}
?>