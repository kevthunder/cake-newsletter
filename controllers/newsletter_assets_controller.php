<?php
class NewsletterAssetsController extends NewsletterAppController {

	var $name = 'NewsletterAssets';
	var $helpers = array('Html', 'Form', 'Newsletter.NewsletterMaker', 'Javascript');
	var $uses = array();
	var $components = array('Newsletter.Funct');
	
	function admin_ajax_get_entry($model=null,$id = null) {
		Configure::write('debug', 0);
		error_reporting(0);
		//Configure::write('debug', 1);
		$this->autoRender = false;
		
		if (!$id || !$model) {
			return false;
		}
		
		////// Get Model //////
		$plugin = $pluginPt = '';
		$modelClass = $model;
		if (strpos($modelClass, '.') !== false) {
			list($plugin, $modelClass) = explode('.', $modelClass);
			$pluginPt = $plugin . '.';
		}
		$modelObj = ClassRegistry::init(array('class' => $pluginPt . $modelClass, 'alias' => $modelClass));
		
		////// Get Data //////
		$modelObj->recursive = 0;
		$data = $modelObj->read(null, $id);
		if(isset($modelObj->multimedia)){
			App::import('Helper', 'photo'); // loadHelper('Html'); in CakePHP 1.1.x.x
        	$photo = new PhotoHelper();
			
			$multimedia = array();
			foreach($modelObj->multimedia as $field => $options){
				foreach($data[$modelClass][$field] as $media){
					$media["url"] = Router::url($media['path'].$media['filename']);
					$media["thumbnail"] = $photo->path($media['path'], $media['filename'], array('method' => 'crop', 'size' => '45x45'));
					$multimedia[] = $media;
				}
			}
			$data['newsletterbox_media'] = $multimedia;
		}
		
		print($this->Funct->json_enc($data));
	}
	
	function admin_popup_entry_search($model=null) {
		$this->layout = 'newletter_popup';
		if (!$model) {
			$this->redirect(array('plugin'=>'newsletter','controller'=>'newsletter','action'=>'index'));
		}
		
		////// Get Model //////
		$plugin = $pluginPt = '';
		$modelClass = $model;
		if (strpos($modelClass, '.') !== false) {
			list($plugin, $modelClass) = explode('.', $modelClass);
			$pluginPt = $plugin . '.';
		}
		$modelObj = ClassRegistry::init(array('class' => $pluginPt . $modelClass, 'alias' => $modelClass));
	
		////// Get Data //////
		$showFields = array(
			'id',
			'displayField',
			'title',
			'desc',
		);
		if(!empty($modelObj->commonFields)){
			$showFields = array_merge($showFields,(array)$modelObj->commonFields);
		}
		$lang = Configure::read('Config.language');
		$finalShowFields = array();
		array_search('displayField',$finalShowFields);
		foreach($showFields as $field){
			if($modelObj->hasField($field)){
				$finalShowFields[] = $field;
			}
			if($lang && $modelObj->hasField($field.'_'.$lang)){
				$finalShowFields[] = $field.'_'.$lang;
			}
		}
		if(in_array('displayField',$showFields) && !in_array($modelObj->displayField,$finalShowFields)){
			if($modelObj->hasField($modelObj->displayField.'_fre')){
				$finalShowFields[] = $modelObj->displayField.'_fre';
			}elseif($modelObj->hasField($modelObj->displayField)){
				$finalShowFields[] = $modelObj->displayField;
			}
		}
		$modelObj->recursive = 0;
		$this->paginate['fields'] = $finalShowFields;
		
		$q = null;
		if(isset($this->data['Search']['q']) && !empty($this->data['Search']['q'])) {
			$q = $this->data['Search']['q'];
			$this->params['named']['q'] = $this->data['Search']['q'];
		}
		elseif(isset($this->params['named']['q']) && !empty($this->params['named']['q'])) {
			$q = $this->params['named']['q'];
		}
		
		if($q != null) {
			foreach($finalShowFields as $field){
				$this->paginate['conditions']['OR'][$modelClass . '.'.$field.' LIKE'] = '%'.$q.'%';
			}
		}
		
		$this->set('fields', $finalShowFields);
		$this->set('data', $this->paginate($modelObj));
		
		
		$this->set('modelName', $modelClass);
	}
}
?>