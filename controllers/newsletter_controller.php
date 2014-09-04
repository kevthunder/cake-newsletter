<?php


class NewsletterController extends NewsletterAppController {

	var $name = 'Newsletter';
	var $helpers = array('Html', 'Form', 'Newsletter.NewsletterMaker', 'Javascript');
	var $uses = array('Newsletter.Newsletter','Newsletter.NewsletterBox','Newsletter.NewsletterSendlist','Newsletter.NewsletterEmail','Newsletter.NewsletterSended','Newsletter.NewsletterEvent');
	var $components = array('Email','Newsletter.NewsletterFunct', 'RequestHandler', 'Session','Acl');
	
	function index() {
	   
		$this->paginate['order'] = 'date DESC';
		$this->set('newsletters', $this->paginate());
	}

	function view($id = null) {
		//$this->autoLayout = false;
		$this->layout = "empty";
		$sended = $admin = null;
		if (!$id) {
			$this->Session->setFlash(__d('newsletter','Invalid Newsletter.', true));
			debug('Invalid Newsletter.');
			$this->redirect(array('action'=>'index'));
		}
		if(
			isset($this->user['User']['id']) 
			&& is_numeric($this->user['User']['id']) 
			&& $this->Acl->check(array('model' => 'User', 'foreign_key' => $this->user['User']['id']), 'admin')
		) {
			$admin = true;
			$this->Newsletter->checkActive = false;
		}
		if(!empty($this->params['named']['sended_id'])){
			$sended = $this->NewsletterSended->read(null, $this->params['named']['sended_id']);
		}
		$newsletter = $this->Newsletter->read(null, $id);
		if(!$newsletter) $this->cakeError('error404');
		
		if(!$this->Newsletter->validRender($newsletter)){
			$newsletter['Newsletter']['html'] = $this->NewsletterFunct->renderNewsletter($id);
		}
		if($newsletter['Newsletter']['TemplateConfig']){
			$newsletter['Newsletter']['TemplateConfig']->beforeView($newsletter,$sended,$admin);
		}
		$this->set('Newsletter', $newsletter);
		$this->set('sended', $sended);
	}
	function redir($url=null,$sended_id=null){
		$this->autoRender = false;
		
		///////// Decode /////////
		$url = base64_decode(str_replace('-','/',$url));
		$replace = array(
			'%sended_id%' => $sended_id,
			'%email%' => '',
		);
		if(!empty($sended_id)){
			$sended = $this->NewsletterSended->read(null,$sended_id);
			if(!empty($sended)){
				$replace['%email%'] = $sended['NewsletterSended']['email'];
			}
		}
		$url = str_replace(array_keys($replace),array_values($replace),$url);
		
		
		///////// check invalid url /////////
		if(!empty($url) && ($url[0] == '#' || $url[0] == '?')){
			$url = null;
		}
		
		///////// Save stats if sended_id is present /////////
		if($sended_id){
			$this->NewsletterEvent->create();
			$visite = array();
			$visite['sended_id'] = $sended_id;
			$visite['date'] = date('Y-m-d H:i:s');
			$visite['action'] = 'click';
			$visite['url'] = $url;
			$visite['ip_address'] = $_SERVER['REMOTE_ADDR'];
			$visite['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
			$this->NewsletterEvent->save($visite);
		}
		//debug($this->params);
		
		
		///////// If internal url, add Google Analytics tracking /////////
		$internal = !preg_match('/http[s]?\:\/\//',$url);
		if(!$internal){
			$internalPrefixes = array(
				Router::url('/',true)
			);
			$contentUrl = NewsletterConfig::load('contentUrl');
			if($contentUrl){
				$internalPrefixes[] = $contentUrl;
			}
			foreach($internalPrefixes as $prefix){
				if(substr($url,0,strlen($prefix)) == $prefix){
					$internal = true;
					break;
				}
			}
		}
		if($internal){
			$url= $url."?utm_source=newsletter&utm_medium=email&utm_campaign=email";
		}
		
		if($url){
			$this->redirect($url);
		}else{
			$this->Session->setFlash(__d('newsletter','Invalid URL.', true));
			$this->redirect('/');
		}
	}
	function counter($sended_id=null,$img_url=null){
		//Configure::write('debug', 1);
		if($sended_id){
			$this->NewsletterEvent->create();
			$visite = array();
			$visite['sended_id'] = $sended_id;
			$visite['date'] = date('Y-m-d H:i:s');
			$visite['action'] = 'view';
			$visite['ip_address'] = $_SERVER['REMOTE_ADDR'];
			$visite['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
			$this->NewsletterEvent->save($visite);
		}
		if($img_url){
			$img_path = WWW_ROOT . str_replace('>',DS,$img_url);
		}else{
			$img_path = APP . 'plugins'. DS .'newsletter'. DS .'webroot'. DS .'img'. DS .'blank.gif';
		}
		if(file_exists($img_path)){
			$path_parts = pathinfo($img_path);
			//ob_clean();
			//debug($path_parts);
			//return $this->render(false);
			$this->view = 'Media';
			$params = array(
				  'id' => $path_parts['basename'],
				  'name' => $path_parts['filename'],
				  'download' => true,
				  'extension' => $path_parts['extension'],
				  'path' => $path_parts['dirname'] . DS
			);
			$this->set($params);
		}else{
			debug('image not found : '.$img_path);
			$this->cakeError('error404');
		}
	}
	function unsubscribe($sended_id=null) {
		//Configure::write('debug', 1);
		//$view = 'unsubscribe_step1';
		if(!empty($this->data) && isset($this->data['NewsletterEmail']['confirm'])){
			if($this->data['NewsletterEmail']['confirm']){
				if($this->data['NewsletterEmail']['email']){
					$count = $this->NewsletterFunct->disable_email($this->data['NewsletterEmail']['email']);
					if($count){
						$view = 'unsubscribe_step3';
					}else{
						$this->Session->setFlash(__d('newsletter','An error occurred, please try again.', true));
						$view = 'unsubscribe_step1';
					}
				}else{
					$this->Session->setFlash(__d('newsletter','An error occurred, please try again.', true));
					$view = 'unsubscribe_step1';
				}
			}else{
				$this->redirect('/');
			}
		}elseif($sended_id || isset($this->data['NewsletterEmail']['email'])){
			$this->NewsletterEmail->recursive = -1;
			$str_email = null;
			$email_id = null;
			if(isset($this->data['NewsletterEmail']['email'])){
				$str_email = $this->data['NewsletterEmail']['email'];
			}elseif($sended_id){
				$sended = $this->NewsletterSended->read(null, $sended_id);
				if(empty( $sended['NewsletterSended']['tabledlist_id'])){
					$email_id = $sended['NewsletterSended']['email_id'];
				}
				$str_email = $sended['NewsletterSended']['email'];
			}
			if($str_email || $email_id){
				if($email_id){
					$email = $this->NewsletterEmail->read(null,$email_id);
				}else{
					$email = $this->NewsletterEmail->find('first', array('conditions'=>array('email'=>$str_email),'order'=>array('active DESC')));
					if(empty($email) || !$email['NewsletterEmail']['active']){
						$tabledEmail = $this->NewsletterFunct->getTabledEmail($str_email);
						if(!empty($tabledEmail)){
							$email = array('NewsletterEmail'=>$tabledEmail);
						}
					}
				}
			}
			if(!empty($email)){
				$this->data = $email;
				if($email['NewsletterEmail']['active']){
					$view = 'unsubscribe_step2';
				}else{
					$this->Session->setFlash(__d('newsletter','This email has allready been disabled.', true));
					$view = 'unsubscribe_step1';
					unset($this->data['NewsletterEmail']['id']);
				}
			}else{
				$this->Session->setFlash(__d('newsletter','Email not found.', true));
				$view = 'unsubscribe_step1';
			}
		}else{
			$view = 'unsubscribe_step1';
		}
		//$this->plugin = '';
		//$this->params['plugin'] = '';
		$this->render($view);
	}
	
	function decline($sended_id=null){
		$newsletter = null;
		if(!empty($sended_id)){
			$this->NewsletterSended->Behaviors->attach('Containable');
			$this->NewsletterSended->contain = array('Newsletter');
			$newsletter = $this->NewsletterSended->read(null, $sended_id);
		}elseif(!empty($this->params['named']['newsletter_id'])){
			if(
				isset($this->user['User']['id']) 
				&& is_numeric($this->user['User']['id']) 
				&& $this->Acl->check(array('model' => 'User', 'foreign_key' => $this->user['User']['id']), 'admin')
			) {
					$this->Newsletter->checkActive = false;
			}
			$this->Newsletter->recursive = -1;
			$newsletter = $this->Newsletter->read(null, $this->params['named']['newsletter_id']);
		}
		$this->set('sended', $newsletter);
	}
	
	function beforeFilter(){
		Cache::config('newsletter_task', array(
			'engine' => 'File',
			'duration'=> '+1 hours',
		));
		if(!empty($this->params['admin']) && !NewsletterConfig::load('cron') && !Cache::read('newsletter_task','newsletter_task')){
			App::import('Lib', 'Newsletter.NewsletterTask');
			NewsletterTask::sync();
			Cache::write('newsletter_task',1,'newsletter_task');
		}
		parent::beforeFilter();
	}
	
	function admin_index() {
		$this->Newsletter->recursive = 0;
		$this->paginate['order'] = 'date DESC';
		$this->paginate['fields'] = $this->Newsletter->minFields();
		$res = $this->paginate();
		if(!empty($res)){
			$ids = Set::extract('{n}.Newsletter.id',$res);
			$findOpt = array(
				'fields'=>array(
					'Newsletter.id',
					"COUNT(DISTINCT CASE WHEN ".$this->Newsletter->NewsletterSending->getPendingCond(true)." THEN `".$this->Newsletter->NewsletterSending->alias."`.`id` END) AS pending_sendings",
					"COUNT(DISTINCT CASE WHEN ".$this->Newsletter->NewsletterSending->getScheduledCond(true)." THEN `".$this->Newsletter->NewsletterSending->alias."`.`id` END) AS scheduled_sendings"
				),
				'conditions'=>array(
					'Newsletter.id' => $ids
				),
				'joins' => array(
					array(
						'alias' => $this->Newsletter->NewsletterSending->alias,
						'table'=> $this->Newsletter->NewsletterSending->useTable,
						'type' => 'left',
						'conditions' => array(
							$this->Newsletter->NewsletterSending->alias.'.newsletter_id = Newsletter.id'
						)
					)
				),
				'group'=>'Newsletter.id',
				'recursive'=>-1
			);
			$stats = $this->Newsletter->find('all',$findOpt);
			$map = array_flip($ids);
			foreach($stats as $stat){
				$res[$map[$stat['Newsletter']['id']]]['Newsletter'] = array_merge(
					$res[$map[$stat['Newsletter']['id']]]['Newsletter'],
					$stat[0]
				);
			}
		};
		$this->set('pluginVersion', $this->pluginVersion);
		$this->set('newsletters', $res);
		$this->set('sendlists', $this->NewsletterSendlist->find('all',array('conditions'=>array('NewsletterSendlist.active'=>1),'recursive'=>-1)));
	}
	function admin_view($id = null) {
		//$this->autoLayout = false;
		if (!$id) {
			$this->Session->setFlash(__d('newsletter','Invalid Newsletter.', true));
			debug('Invalid Newsletter.');
			$this->redirect(array('action'=>'index'));
		}
		$newsletter = $this->Newsletter->read(null, $id);
		if(!$this->Newsletter->validRender($newsletter)){
			$newsletter['Newsletter']['html'] = $this->NewsletterFunct->renderNewsletter($id);
		}
		$this->layout = "empty";
		
		if($newsletter['Newsletter']['TemplateConfig']){
			$newsletter['Newsletter']['TemplateConfig']->beforeView($newsletter,null,true);
		}
		$this->set('Newsletter', $newsletter);
	}
	
	
	function admin_add() {
		if (!empty($this->data)) {
			$this->Newsletter->create();
			if ($this->Newsletter->save($this->data)) {
				$id = $this->Newsletter->getLastInsertId();
				$this->Session->setFlash(__d('newsletter','The Newsletter has been saved', true));
				$this->redirect(array('action'=>'edit',$id));
			} else {
				$this->Session->setFlash(__d('newsletter','The Newsletter could not be saved. Please, try again.', true));
			}
		}
		
		$langs = NewsletterConfig::load('langs');
		$this->set('langs',$langs);
		$this->set('templates',$this->NewsletterFunct->getTemplates());
	}

	function admin_edit($id = null) {
		//Configure::write('debug', 1);
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(__d('newsletter','Invalid Newsletter', true));
			$this->redirect(array('action'=>'index'));
		}
		$newsletter = $this->Newsletter->read(null, $id);
		//debug($newsletter);
		if (!empty($this->data)) {
			if(!empty($this->data['NewsletterBox'])){
				foreach($this->data['NewsletterBox'] as $newsletter_box){
					$this->NewsletterBox->save($newsletter_box);
				}
			}
			if(empty($this->data['Newsletter']['associated'])){
				$this->data['Newsletter']['associated'] = array();
			}
			$this->Newsletter->set($this->data);
			if ($this->Newsletter->validates()) {
				$this->data['Newsletter']['html'] = $this->NewsletterFunct->renderNewsletter($this->Newsletter->data);//$this->requestAction('admin/newsletter/newsletter/make/'.$id);
				$this->Newsletter->NewsletterVariant->updateAll(array('NewsletterVariant.html'=>null), array('NewsletterVariant.newsletter_id'=>$id));
				
				$this->data['Newsletter']['tested'] = 0;
				if ($this->Newsletter->save($this->data)) {
					$this->Session->setFlash(__d('newsletter','The Newsletter has been saved', true));
					$this->Session->delete('EditedNewsletter');
					$this->redirect(array('action'=>'index'));
				} else {
					$this->Session->setFlash(__d('newsletter','The Newsletter could not be saved. Please, try again.', true));
				}
			} else {
				$this->Session->setFlash(__d('newsletter','The Newsletter could not be saved. Please, try again.', true));
			}
		}
		if (empty($this->data)) {
			$this->data = $newsletter;
		}
		$this->NewsletterBox->recursive = -1;
		$boxes_by_zone = $this->NewsletterFunct->getBoxByZone($id);
		
		$langs = NewsletterConfig::load('langs');
		if(!empty($langs)){
			$newsletterByLang = $this->Newsletter->find('list',array('fields'=>array('id','title','lang'),'conditions'=>array('id NOT'=>$id,'Newsletter.lang IS NOT NULL'), 'recursive' => -1));
			$this->set('newsletterByLang',$newsletterByLang);
		}
		$config = $this->Newsletter->getConfig($this->data);
		if(!empty($config)){
			$config->beforeRenderEdit($this->data,$this);
		}
		
		$template_error = false;
		$config = $this->Newsletter->getConfig($this->data);
		if(!empty($config)){
			if(!$config->check()){
				$template_error = 'outdated';
			}
			$config->beforeRender($this->data,$this);
		}else{
			$template_error = 'missing';
		}
		$this->set('template_error',$template_error);
		
		$this->set('template_config',$config);
		$this->set('langs',$langs);
		$this->set('newsletter',$this->data);
		$this->set('boxes_by_zone',$boxes_by_zone);
		$this->set('templates',$this->NewsletterFunct->getTemplates());
		$this->set('box_elements',$this->NewsletterFunct->getBoxElements($this->data['Newsletter']['template']));
	}
	function admin_get_box_edit($id) {
		if(Configure::read('debug')==2){
			Configure::write('debug', 1);
		}
		$this->layout = "newsletter_box_edit_ajax";
		
		if(is_array($id)){
			$newsletter_box = $id;
		}else{
			$newsletter_box = $this->NewsletterBox->read(null, $id);
		}
		
		$newsletter = $this->Newsletter->read(null, $newsletter_box["NewsletterBox"]["newsletter_id"]);
		$this->set('newsletter_box',$newsletter_box);
		$this->data = $newsletter_box;
		$this->set('newsletter',$newsletter);
		
		$config = $this->NewsletterBox->getConfig($newsletter_box);
		if(!empty($config)){
			$config->beforeRenderEdit($newsletter_box,$this);
		}
		$single = preg_match('/^single--/',$newsletter_box['NewsletterBox']['zone']);
		$this->render(NewsletterConfig::getNewsletterBoxPaths($newsletter_box['NewsletterBox']['template'],$newsletter['Newsletter']['template'],true,true,$single));
	}
	function admin_add_box($newsletter_id,$zone,$boxElement = null) {
		$this->autoRender = false;
		if(Configure::read('debug')==2){
			Configure::write('debug', 1);
		}
		//debug($this->params);
		$this->layout = "ajax";
		
		$zoneOpt = $this->_getZoneOpt($newsletter_id,$zone);
		
		if(!empty($zoneOpt['boxList'])){
			if(empty($boxElement) && count($zoneOpt['boxList']) == 1){
				$boxElement = key($zoneOpt['boxList']);
			}
		}
		
		if($boxElement){
			$this->NewsletterBox->create();
			$newsletter_box = array("NewsletterBox"=>array());
			$newsletter_box["NewsletterBox"]["template"] = $boxElement;
			$newsletter_box["NewsletterBox"]["newsletter_id"] = $newsletter_id;
			$newsletter_box["NewsletterBox"]["zone"] = $zone;
			if(!empty($zoneOpt['boxList'][$boxElement]['data'])){
				$newsletter_box["NewsletterBox"]['data'] = $zoneOpt['boxList'][$boxElement]['data'];
			}
			$this->NewsletterBox->save($newsletter_box);
			$id = $this->NewsletterBox->getLastInsertID();
			$newsletter_box["NewsletterBox"]["id"] = $id;
			
			if(!empty($this->params['named']['mode']) && $this->params['named']['mode'] == 'edit'){
				$this->admin_get_box_edit($id);
			}else{
				$this->data = $newsletter_box;
				$newsletter = $this->Newsletter->read(null, $newsletter_id);
				$this->set('newsletter_box',$newsletter_box);
				$this->set('newsletter',$newsletter);
				//$this->render('/elements/newsletter_box/'.$boxElement);
				
				$this->render('show_box');
			}
		}else{
			echo "No template selected";
			//debug($zoneOpt);
		}
	}
	
	function admin_edit_box($id = null){
		if(Configure::read('debug')==2){
			Configure::write('debug', 1);
		}
		$this->layout = "ajax";
		
		$newsletter_box = $this->NewsletterBox->read(null, $id);
		if (!empty($this->data)) {
			//debug($this->data);
			if(Configure::read('App.encoding') && strtolower(Configure::read('App.encoding')) != "utf-8" && $this->RequestHandler->isAjax()){
				$this->data = $this->NewsletterFunct->array_map_recursive("utf8_decode",$this->data);
			}
			//////// Gestion de fichiers ////////
			if(isset($this->data["NewsletterBox"]["file"])){
				//debug($this->data);
				if(isset($newsletter_box["NewsletterBox"]["file"])){
					$uploaded_files = $newsletter_box["NewsletterBox"]["file"];
				}else{
					$uploaded_files = array();
				}
				$files = $this->data["NewsletterBox"]["file"];
				foreach($files as $name => $file){
					if(isset($file['error']) && $file['error'] == 0) {
						$uploaded_files[$name] = $this->NewsletterFunct->upload($file);
						//debug($uploaded_files[$name]);
					} elseif(isset($file['del']) && $file['del']){
						unset($uploaded_files[$name]);
					}
				}
				if(count($uploaded_files)){
					$this->data["NewsletterBox"]["file"] = $uploaded_files;
				}else{
					$this->data["NewsletterBox"]["file"] = null;
				}
			}
			
			//////// save ////////
			//$this->data = $this->NewsletterFunct->encode_box($this->data);
			//debug($this->data);
			if ($this->NewsletterBox->save($this->data)) {

			}
		}
		$newsletter_box = $this->NewsletterBox->read(null, $id);
		$newsletter = $this->Newsletter->read(null, $newsletter_box["NewsletterBox"]["newsletter_id"]);
		$this->set('newsletter_box',$newsletter_box);
		$this->set('newsletter',$newsletter);
		//$this->render('/elements/newsletter_box/'.$newsletter_box["NewsletterBox"]["template"]);
		
		$this->render('show_box');
	}
	
	function admin_reset_box($id = null){
		if(Configure::read('debug')==2){
			Configure::write('debug', 1);
		}
		$this->layout = "ajax";
		
		if ($id) {
			$newsletter_box = $this->NewsletterBox->read(null, $id);
			$newsletter = $this->Newsletter->read(null, $newsletter_box["NewsletterBox"]["newsletter_id"]);
			
			$newsletter_box['NewsletterBox']['id'] = null;
			$newsletter_box['NewsletterBox']['data'] = array();
			$newsletter_box['NewsletterBox']['file'] = array();
			
			$this->set('newsletter_box',$newsletter_box);
			$this->set('newsletter',$newsletter);
		
			$this->NewsletterBox->delete($id);
		
			$this->render('show_box');
		}
	}
	
	function admin_delete_box($id = null){
		if ($id) {
			$this->NewsletterBox->delete($id);
		}
		$this->autoRender = false;
	}
	
	function _getZoneOpt($newsletter_id,$id){
		$session = $this->Session->read('EditedNewsletter.'.$newsletter_id.'.zone.'.$id); 
		if($session) return $session;
		return NewsletterConfig::getDefZoneOpt();
	}
	
	function admin_delete($id = null) {
		if (!$id) {
			$this->Session->setFlash(__d('newsletter','Invalid id for Newsletter', true));
			$this->redirect(array('action'=>'index'));
		}
		if ($this->Newsletter->delete($id)) {
			$this->Session->setFlash(__d('newsletter','Newsletter deleted', true));
			$this->redirect(array('action'=>'index'));
		}
	}
	
	function admin_invalidate_render(){
		$this->Newsletter->updateAll(array('Newsletter.html'=>null), array(1));
		$this->Newsletter->NewsletterVariant->updateAll(array('NewsletterVariant.html'=>null), array(1));
		
		$this->Session->setFlash(__d('newsletter','All the newsletter renders has been cleared', true));
		$this->redirect(array('action'=>'index'));
			
		$this->render(false);
	}
	
	function admin_import_template(){
		App::import('Lib', 'Newsletter.NewsletterTemplateImporter');
		
		$available = NewsletterTemplateImporter::checkRequirements();
		$this->set('available',$available);
		
		if($available){
			if (!empty($this->data)) {
				if(!empty($this->data['Newsletter']['zip_file']) && $this->data['Newsletter']['zip_file']['error'] == 0  && pathinfo($this->data['Newsletter']['zip_file']['name'],PATHINFO_EXTENSION) == 'zip'){
					$i = 0;
					while(file_exists(TMP.'newsletter'.DS.'import_'.$i.'.zip')){
						$i++;
					}
					if(move_uploaded_file($this->data['Newsletter']['zip_file']['tmp_name'],TMP.'newsletter'.DS.'import_'.$i.'.zip')){
						$importer = new NewsletterTemplateImporter(TMP.'newsletter'.DS.'import_'.$i.'.zip',$this->data['Newsletter']['title']);
						if($importer->process($error)){
							$this->Session->setFlash(__d('newsletter','Newsletter template imported', true));
							$this->redirect(array('action'=>'index'));
						}else{
							$this->Session->setFlash(__d('newsletter','Error reading zip file', true).' : '.$error);
						}
					}else{
						$this->Session->setFlash(__d('newsletter','Error moving zip file', true));
					}
				}else{
					$this->Session->setFlash(__d('newsletter','Error uploading zip file', true));
				}
			}
			
		}
	}
}
?>