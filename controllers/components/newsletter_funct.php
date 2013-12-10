<?php 
class NewsletterFunctComponent extends Object
{
	var $controller = null;
	var $Newsletter = null;
	function initialize(&$controller, $settings = array()) {
		uses('Folder');
		$this->Folder =& new Folder();
		$this->controller =& $controller;
		if(!empty($this->controller->Newsletter)){
			$this->Newsletter = $this->controller->Newsletter;
		}else{
			$this->Newsletter = ClassRegistry::init('Newsletter.Newsletter');
		}
		
	}
	function slug($chaine="", $length=150){
		// remplace les caractères accentués par leur version non accentuée
		$id = strtr( $chaine,
				'ŠŽšžŸÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÑÒÓÔÕÖØÙÚÛÜÝàáâãäåçèéêëìíîïñòóôõöøùúûüýÿ',
				'SZszYAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy' );

		// remplace les caractères non standards
		$id = preg_replace(
				array(
					'`^[^A-Za-z0-9]+`',
					'`[^A-Za-z0-9]+$`',
					'`[^A-Za-z0-9]+`' ),
				array('','','-'),
				$id );
				
		return substr(strtolower($id),0, $length);
	}
	function array_map_recursive($func, $arr) {
		$newArr = array();
		foreach( $arr as $key => $value ) {
			$newArr[ $key ] = ( is_array( $value ) ? $this->array_map_recursive( $func, $value ) : $func( $value ) );
		}
		return $newArr;
	}
	function getAllViewPaths(){
		if(!isset($this->allViewPaths)){
		
			$templates = array();
			$paths = App::path('views');
			$pluginsPaths = App::path('plugins');
			foreach($pluginsPaths as $path) {
				if($this->Folder->cd($path)){
					$pluginPaths = $this->Folder->read();
					foreach($pluginPaths[0] as $pluginPath){
						array_push($paths,$path.$pluginPath.DS.'views'.DS);
					}
				}
			}
			$this->allViewPaths = $paths;
		}else{
			$paths = $this->allViewPaths;
		}
		return $paths;
	}
	
	function getTemplates(){
		$configs = $this->getTemplatesConfig();
		
		$templates = array();
		$ignores = NewsletterConfig::load('hiddenTemplates');
		foreach($configs as $name=>$config) {
			if(empty($ignores) || !in_array($name,$ignores)){
				$templates[$name] = $config->getLabel();
			}
		}
		return $templates;
	}
	
	function getTemplatesConfig(){
		$paths = $this->getAllViewPaths();
		foreach($paths as $path) {
			if($this->Folder->cd($path.'/elements/newsletter')){
				$templateFiles = $this->Folder->find('.+\.ctp$');
				foreach($templateFiles as &$file){
					$name = basename($file, ".ctp");
					$config = ClassCollection::getObject('NewsletterConfig',$name);
					$templates[$name] = $config;
				}
			}
		}
		return $templates;
	}
	
	function getBoxElements($template = null){
		$paths = $this->getAllViewPaths();
		$subPaths = array('/elements/newsletter_box');
		if(!empty($template)) $subPaths[] = '/elements/newsletter_box/'.$template;
		foreach($paths as $path) {
			foreach($subPaths as $subPath) {
				if($this->Folder->cd($path.$subPath)){
					$templateFiles = $this->Folder->find('.+\.ctp$');
					foreach($templateFiles as &$file){
						if(!preg_match("/_edit.ctp$/",$file)){
							$name = basename($file, ".ctp");
							$config = ClassCollection::getObject('NewsletterBoxConfig',$name);
							$boxElements[$name] = $config->getLabel();
							//$boxElements[$name] = $name;
						}
					}
				}
			}
		}
	
		return $boxElements;
	}
	
	function getBoxByZone($newsletter){
		if(!is_numeric($newsletter)){
			if(!empty($newsletter['Newsletter']['id'])){
				$newsletter = $newsletter['Newsletter']['id'];
			}elseif(!empty($newsletter['id'])){
				$newsletter = $newsletter['id'];
			}else{
				return null;
			}
		}
		$newsletter_boxes = $this->Newsletter->NewsletterBox->find('all',array('conditions'=>array('NewsletterBox.newsletter_id'=>$newsletter),'order'=>'NewsletterBox.zone ASC, NewsletterBox.order ASC'));
		$boxes_by_zone = array();
		foreach($newsletter_boxes as $box){
			$boxes_by_zone[$box['NewsletterBox']['zone']][] = $box;
		}
		return $boxes_by_zone;
	}
	
	function renderNewsletter(&$newsletter,$save=true,&$variant=null){
		if(is_numeric($newsletter)){
			$tmp = $this->Newsletter->read(null, $newsletter);
			$newsletter =& $tmp;
		}
		if(empty($newsletter)){
			debug('Invalid Newsletter.');
			return null;
		}
		if(!empty($variant) && is_numeric($variant)){
			$variant = $this->Newsletter->NewsletterVariant->read(null, $variant);
		}
		$boxes_by_zone = $this->getBoxByZone($newsletter);
		$config = $this->Newsletter->getConfig($newsletter);
		if(!empty($config)){
			$config->beforeRender($newsletter,$this->controller);
		}
			
		$vars = array(
			'newsletter' => $newsletter,
			'boxes_by_zone' => $boxes_by_zone,
			'newsletter_data' => $newsletter,
			'title_for_newsletter' => '<span id="title_for_newsletter">'.$newsletter['Newsletter']['title'].'</span>',
			'edit_mode' => false,
			'variant' => $variant,
		);
		$this->controller->set($vars);
		
		
		$viewClass = $this->controller->view;
		if ($viewClass != 'View') {
			list($plugin, $viewClass) = pluginSplit($viewClass);
			$viewClass = $viewClass . 'View';
			App::import('View', $this->controller->view);
		}
		$View = new $viewClass($this->controller, true);
		
		$View->layout = 'newsletter';
		
		$htmlContent = $View->element('newsletter'.DS.$newsletter['Newsletter']['template'], array(), true);
		$html = $View->renderLayout($htmlContent);
		
		ClassRegistry::removeObject('view');
		
		
		if(!empty($variant)){
			$variant['NewsletterVariant']['html'] = $html;
			if($save){
				$this->Newsletter->NewsletterVariant->save(array('NewsletterVariant'=>$variant['NewsletterVariant']));
			}
		}else{
			$newsletter['Newsletter']['html'] = $html;
			if($save){
				$this->Newsletter->save(array('Newsletter'=>$newsletter['Newsletter']));
			}
		}
		
		
		return $html;
	}
	function json_enc($value){
		//return json_encode($this->recur_utf8_encode($value));
		return json_encode($value);
	}
	function json_dec($value){
		//return $this->recur_utf8_decode(json_decode($value,true));
		return json_decode($value,true);
	}
	function recur_utf8_encode($value){
		if(is_string($value)){
			$value = utf8_encode($value);
		}elseif(is_array($value)){
			foreach($value as &$val){
				$val = $this->recur_utf8_encode($val);
			}
		}
		return $value;
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
	
	function get_emails($newsletterSendlist){
		$emails = array();
		foreach($newsletterSendlist['NewsletterEmail'] as $newsletterEmail){
			if($newsletterEmail['active']){
				if($newsletterEmail['name']){
					array_push($emails,$newsletterEmail['name'].' <'.$newsletterEmail['email'].'>');
				}else{
					array_push($emails,$newsletterEmail['email']);
				}
			}
		}
		return $emails;
	}
	function findType($ext) {
		$typesCat = array(
					 'image' => '.jpg .jpeg .gif .png .bmp',
					 'movie' => '.mov .avi .mpg .mpeg .wmv',
					 'swf' => '.swf',
					 'file' => '*'
					 );
		$type = null;
		foreach ($typesCat as $key => $val) {
			if($val == '*' || in_array($ext,explode(' ',$val))){
				$type = $key;
				break;
			}
		}
		return $type;
	}
	function upload(&$data, $path = null) {
		if(is_array($data)) {
			if($path == null) {
				$path = 'files'.DS.'newsletter'.DS;
			}
			if(substr($path, -1) != DS) $path .= DS;
			/*
			$target_path = WWW_ROOT . $path;
			$target_path = $target_path . basename($data['name']);
			$fileName = basename($data['name']);
			*/
			$target_path = WWW_ROOT . $path;
			$fileName = basename($data['name']);
			$fileExt = strrchr($fileName, '.');
			$name = substr($fileName, 0, -strlen($fileExt));
			$name = $this->slug($name, 150);
			$fileName = $name.$fileExt;
			$target_path = $target_path . $fileName;
			
			
			if(!file_exists(WWW_ROOT . $path)){
				if(!mkdir(WWW_ROOT . $path,  0777)){
					return false;
				}
			}
			
			$i = 1;
			while(file_exists(WWW_ROOT . $path . $fileName)) {
				/*
				$fileNameBase = substr(basename($data['name']), 0, -4);
				$fileExt = substr(basename($data['name']), -4);
				$fileName = $fileNameBase . '_' . $i .  $fileExt;
				$target_path = WWW_ROOT . $path . $fileName;
				*/
				$fileNameBase = basename($data['name']);
				$fileExt = strrchr($fileNameBase, '.');
				$name = substr($fileNameBase, 0, -strlen($fileExt));
				$name = $this->slug($name, 150);
				$fileName = $name . '_' . $i .  $fileExt;
				$target_path = WWW_ROOT . $path . $fileName;
				
				$i++;
			}
			if(move_uploaded_file($data['tmp_name'], $target_path)) {
				//$this->create();
				//$picture = array('id' => null, 'title' => $fileName, 'file' => $fileName, 'path' => $path);
				//$this->save($picture);
				//return $this->getLastInsertID();
				$path = '/'.str_replace(DS,'/',$path);
				return array('file' => $fileName, 'path' => $path, 'ext'=>$fileExt, 'type'=>$this->findType($fileExt));
			}
			else {
				return false;
			}
		}
		else {
			//return $data;
			return false;
		}
	}
	
	function getTableSendlistMails($tableSendlist_id,$active = true,$addfindOptions = null){
		$emails = array();
		$tableSendlist = $this->getTableSendlistID($tableSendlist_id,true);
		if(!empty($tableSendlist)){
			$Model = $tableSendlist['modelClass'];
			$modelName = $Model->alias;
			
			$findOptions = $this->tabledEmailGetFindOptions($tableSendlist,$active,$addfindOptions);
			
			$Model->recursive = $tableSendlist['recursive'];
			$mails = $Model->find('all',$findOptions);
			foreach($mails as $mail){
				$email = $this->tabledEmailGetFields($mail,$tableSendlist);
				if(!empty($email)){
					$emails[] = $email;
				}
			}
		}
		return $emails;
	}
	function getTableSendlistID($tableSendlist_id,$getModel = false){
		if(is_array($tableSendlist_id)){
			return $tableSendlist_id;
		}
		$tableSendlists = Configure::read('Newsletter.tableSendlist');
		if(!empty($tableSendlists)){
			foreach($tableSendlists as $key => $tableSendlist){
				if(isset($tableSendlist['id'])){
					if($tableSendlist['id'] == $tableSendlist_id){
						return $this->getTableSendlist($tableSendlists[$key],$key,$getModel);
					}
				}elseif($key==$tableSendlist_id){
					return $this->getTableSendlist($tableSendlists[$key],$key,$getModel);
				}
			}
		}
		return null;
	}
	function getTableSendlist($tableSendlist,$id = null,$getModel = false){
		if(!empty($tableSendlist)){
			$defaultOpt = array(
					'emailField'=>'email',
					'nameField'=>'name',
					'activeField'=>'active',
					'showInnactive'=>true,
					'firstNameField'=>null,
					'lastNameField'=>null,
					'conditions'=>null,
					'allowUnsubscribe'=>true,
					'findOptions'=>null,
					'recursive'=>-1
				);
			if(!is_array($tableSendlist)){
				$tableSendlist = array('model'=>$tableSendlist);
			}
			if(isset($tableSendlist['model']) && $tableSendlist['model']){
				$tableSendlist = array_merge($defaultOpt,$tableSendlist);
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
	function getTableSendlists($getModel = false){
		$tableSendlists = Configure::read('Newsletter.tableSendlist');
		if(!empty($tableSendlists)){
			$sendlists = array();
			foreach($tableSendlists as $key => $tableSendlist){
				$tableSendlist = $this->getTableSendlist($tableSendlist,$key,$getModel);
				if(isset($tableSendlist)){
					$sendlists[$tableSendlist['id']] = $tableSendlist;
				}
			}
			return $sendlists;
		}else{
			return array();
		}
	}
	function tabledEmailGetFindOptions($tableSendlist,$active = true,$addfindOptions = null){
		$tableSendlist = $this->getTableSendlistID($tableSendlist,true);
		if(!empty($tableSendlist)){
			$Model = $tableSendlist['modelClass'];
			$modelName = $Model->alias;
			$conditions = array();
			if($active && !is_null($tableSendlist['activeField']) && $Model->hasField($tableSendlist['activeField'])){
				$conditions[$modelName.'.'.$tableSendlist['activeField']] = 1;
			}
			$conditions['NOT'][$modelName.'.'.$tableSendlist['emailField']] = "";
			$conditions[] = $modelName.'.'.$tableSendlist['emailField'].' IS NOT NULL';
			if(!empty($tableSendlist['conditions'])){
				if(!array($tableSendlist['conditions'])){
					$tableSendlist['conditions'] = array($tableSendlist['conditions']);
				}
				$conditions = set::merge($conditions,$tableSendlist['conditions']);
			}
			$findOptions = array('conditions'=>$conditions);
			
			$findOptions['model']=$Model;
			
			$findOptions['fields'] = $this->tabledEmailGetFindFields($tableSendlist);
			if(!empty($tableSendlist['findOptions']) && is_array($tableSendlist['findOptions'])){
				$findOptions = set::merge($findOptions,$tableSendlist['findOptions']);
			}
			if(!empty($addfindOptions)){
				$NewsletterEmail = ClassRegistry::init('Newsletter.NewsletterEmail'); 
				App::import('Lib', 'Newsletter.SetMulti');
				$replace = array(
						$NewsletterEmail->alias.'.email' => $modelName.'.'.$tableSendlist['emailField'],
						$NewsletterEmail->alias => $modelName
					);
				$addfindOptions = SetMulti::replaceTree(array_keys($replace),array_values($replace),$addfindOptions);
				$findOptions = set::merge((array)$addfindOptions,$findOptions);
			}
			return $findOptions;
		}
		return null;
	}
	function tabledEmailGetFindFields($tableSendlist){
		$Model = $tableSendlist['modelClass'];
		$modelName = $Model->alias;
		$dbo = $Model->getDataSource();
		$fields = array();
		$fields['id'] = $modelName.'.'.$Model->primaryKey;
		$fields['email'] = $modelName.'.'.$tableSendlist['emailField'];
		if($tableSendlist['firstNameField'] && $Model->hasField($tableSendlist['firstNameField'])){
			$fields['first_name'] = $modelName.'.'.$tableSendlist['firstNameField'];
		}
		if($tableSendlist['lastNameField'] && $Model->hasField($tableSendlist['lastNameField'])){
			$fields['last_name'] = $modelName.'.'.$tableSendlist['lastNameField'];
		}
		if($tableSendlist['nameField'] && $Model->hasField($tableSendlist['nameField'])){
			$fields['name'] = $modelName.'.'.$tableSendlist['nameField'];
		}
		if($tableSendlist['activeField'] && $Model->hasField($tableSendlist['activeField'])){
			$fields['active'] = $modelName.'.'.$tableSendlist['activeField'];
		}
		$fields['primary_key'] = $modelName.'.'.$Model->primaryKey;
		//$fields[] = '*';
		return $fields;
	}
	function fieldsAddAlias($fields){
		$formated = array();
		foreach($fields as $alias => $field){
			if(is_numeric($alias)){
				$p = explode('.',$field,2);
				$alias = array_pop($p);
			}
			$formated[$alias] = $field;
		}
		return $formated;
	}
	function valFields($fields){
		$formated = array();
		foreach($fields as $alias => $field){
			$formated[$alias] = "'".addslashes($field)."'";
		}
		return $formated;
	}
	function tabledEmailGetFields($mail,$tableSendlist,$useModel=null){
		$Model = $tableSendlist['modelClass'];
		$modelName = $Model->alias;
		
		$emailData = array();
		if(isset($mail[$modelName]['email'])){
			$emailData['email']= $mail[$modelName]['email'];
		}else if($tableSendlist['emailField'] && isset($mail[$modelName][$tableSendlist['emailField']])){
			$emailData['email']= $mail[$modelName][$tableSendlist['emailField']];
		}
		if(isset($emailData['email']) && $emailData['email']){
			//$basicFields = array('id','email','name','first_name','last_name');
			//$emailData = array_intersect_key($mail[$modelName],array_flip($basicFields));
			//if(array_key_exists($Model->primaryKey, $mail[$modelName]) {}
			$emailData['id'] = $mail[$modelName][$Model->primaryKey];
			//debug($mail[$modelName]);
			if($tableSendlist['activeField'] && array_key_exists($tableSendlist['activeField'],$mail[$modelName])){
				$emailData['active']= $mail[$modelName][$tableSendlist['activeField']];
			}else if(array_key_exists('active',$mail[$modelName])){
				$emailData['active']= $mail[$modelName]['active'];
			}else{
				$emailData['active']= 1;
			}
			$name = array();
			if(isset($mail[$modelName]['first_name']) && $mail[$modelName]['first_name']){
				$name[] = $mail[$modelName]['first_name'];
			}else if($tableSendlist['firstNameField'] && isset($mail[$modelName][$tableSendlist['firstNameField']]) && $mail[$modelName][$tableSendlist['firstNameField']]){
				$name[] = $mail[$modelName][$tableSendlist['firstNameField']];
			}
			if(isset($mail[$modelName]['last_name']) && $mail[$modelName]['last_name']){
				$name[] = $mail[$modelName]['last_name'];
			}else if($tableSendlist['lastNameField'] && isset($mail[$modelName][$tableSendlist['lastNameField']]) && $mail[$modelName][$tableSendlist['lastNameField']]){
				$name[] = $mail[$modelName][$tableSendlist['lastNameField']];
			}
			$name = implode(' ',$name);
			if(!$name){
				if(isset($mail[$modelName]['name'])){
					$name = $mail[$modelName]['name'];
				}else if($tableSendlist['nameField'] && isset($mail[$modelName][$tableSendlist['nameField']])){
					$name = $mail[$modelName][$tableSendlist['nameField']];	
				}
			}
			$emailData['name'] = $name;
			$emailData['sendlist_id'] = $tableSendlist['id'];
			$emailData['data'] = $mail;
			if($useModel){
				return array($useModel => $emailData);
			}else{
				return $emailData;
			}
		}
		return false;
	}
	/*function findTabledEmail($options){
		if(!empty($options['tableSendlist'])){
			$tableSendlist = $options['tableSendlist'];
			if($tableSendlist == $tableSendlist*1){
				$tableSendlist = $this->getTableSendlistID($tableSendlist,true);
				if(empty($tableSendlist)){
					return null;
				}
			}
		}
		
	}*/
	function getTabledEmail($email,$tableSendlist=null){
		$curTableSendlist = null;
		if(!empty($tableSendlist)){
			if($tableSendlist == $tableSendlist*1){
				$tableSendlist = $this->getTableSendlistID($tableSendlist,true);
				if(empty($tableSendlist)){
					return null;
				}
			}
		}
		if($email == strval($email*1)){
			//debug($email);
			if(!empty($tableSendlist)){
				$Model = $tableSendlist['modelClass'];
				$mail = $Model->read(null,$email);
				$curTableSendlist = $tableSendlist;
			}
		}else{
			if(!empty($tableSendlist)){
				$tableSendlists = $tableSendlist;
			}else{
				$tableSendlists = $this->getTableSendlists(true);
			}
			reset($tableSendlists);
			while ((list($key, $tableSendlist) = each($tableSendlists)) && empty($mail)) {
				$Model = $tableSendlist['modelClass'];
				$modelName = $Model->alias;
				
				$findOptions = $this->tabledEmailGetFindOptions($tableSendlist,false);
				$findOptions['conditions'][$tableSendlist['emailField']]=$email;
				
				$mail = $Model->find('first', $findOptions);
				$curTableSendlist = $tableSendlist;
			}
		}
		if(!empty($mail)){
			return $this->tabledEmailGetFields($mail,$curTableSendlist);
		}
		return null;
	}
}
?>