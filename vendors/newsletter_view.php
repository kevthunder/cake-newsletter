<?php
class NewsletterView extends View {
	function element($name, $params = array(), $loadHelpers = false) {
		if(!is_array($name)){
			return parent::element($name, $params, $loadHelpers);
		}else{
			$not_found_str = "Not Found";
			//debug($name);
			foreach($name as $name){
				$result = parent::element($name, $params, $loadHelpers);
				if($result && substr($result,0,strlen($not_found_str))!=$not_found_str){
					return $result;
				}
			}
			return $result;
		}
	}
	function _getViewFileName($name = null) {
		if(!is_array($name)){
			return parent::_getViewFileName($name);
		}else{
			//debug($name);
			//die();
			
			$subDir = null;
	
			if (!is_null($this->subDir)) {
				$subDir = $this->subDir . DS;
			}
			
			$paths = array();
			
			foreach($name as $name){
				$name = str_replace('/', DS, $name);
		
				if (strpos($name, DS) === false && $name[0] !== '.') {
					$name = $this->viewPath . DS . $subDir . Inflector::underscore($name);
				} elseif (strpos($name, DS) !== false) {
					if ($name{0} === DS || $name{1} === ':') {
						if (is_file($name)) {
							return $name;
						}
						$name = trim($name, DS);
					} else if ($name[0] === '.') {
						$name = substr($name, 3);
					} else {
						$name = $this->viewPath . DS . $subDir . $name;
					}
				}
				
				$paths = array_merge($paths,$this->_paths(Inflector::underscore($this->plugin)));
				foreach ($paths as $path) {
					if (file_exists($path . $name . $this->ext)) {
						return $path . $name . $this->ext;
					} elseif (file_exists($path . $name . '.ctp')) {
						return $path . $name . '.ctp';
					} elseif (file_exists($path . $name . '.thtml')) {
						return $path . $name . '.thtml';
					}
				}
			}
			
			$defaultPath = $paths[0];
				
			if ($this->plugin) {
				$pluginPaths = Configure::read('pluginPaths');
				foreach ($paths as $path) {
					if (strpos($path, $pluginPaths[0]) === 0) {
						$defaultPath = $path;
						break;
					}
				}
			}
			return $this->_missingView($defaultPath . $name . $this->ext, 'missingView');
		}
	}
}
?>