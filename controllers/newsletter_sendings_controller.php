<?php
class NewsletterSendingsController extends NewsletterAppController {

	var $name = 'NewsletterSendings';
	
	var $helpers = array('Newsletter.NewsletterMaker');
	var $uses = array('Newsletter.NewsletterSending','Newsletter.NewsletterSendlist','Newsletter.NewsletterSended','Newsletter.NewsletterVariant');
	var $components = array('Email','Newsletter.EmailUtils', 'Newsletter.NewsletterFunct', 'RequestHandler');
	
	var $lastProcessTime = null;
	var $consoleOut = '';
	var $consoleGo = null;

	
	function add($newsletter_id = null,$sended_id = null){
		if (!$newsletter_id){
			if (!empty($this->data['NewsletterSending']['newsletter_id'])) {
				$newsletter_id = $this->data['NewsletterSending']['newsletter_id'];
			}elseif (!empty($this->params['named']['newsletter_id'])) {
				$newsletter_id = $this->params['named']['newsletter_id'];
			}else{
				$this->Session->setFlash(__d('newsletter','Invalid Newsletter', true));
				$this->redirect('/');
			}
		}
		App::import('Lib', 'Newsletter.NewsletterConfig');
		$selfSending = NewsletterConfig::load('selfSending');
		if(!$selfSending){
			$this->log('Newsletter : Self sending is disabled',LOG_DEBUG);
			$this->redirect(array('plugin' => 'auth', 'controller' => 'users', 'action' => 'permission_denied', 'admin' => false));
		}
		$newsletter = $this->NewsletterSending->Newsletter->read(null,$newsletter_id);
		if(empty($newsletter) || !$newsletter['Newsletter']['active']){
			$this->Session->setFlash(__d('newsletter','Invalid Newsletter', true));
			$this->redirect('/');
		}
		if (!empty($this->data)) {
			//debug($this->data);
			$this->NewsletterSending->create();
			$this->NewsletterSending->validate['sender_name'] = array(
				'rule' => 'notEmpty',
			);
			$this->NewsletterSending->validate['sender_email'] = array(
				'rule' => 'notEmpty',
			);
			$this->NewsletterSending->validate['additional_emails'] = array(
				'rule' => 'notEmpty2',
			);
			if(!empty($newsletter)){
				$this->data['NewsletterSending']['active'] = 1;
				$this->data['NewsletterSending']['html'] = $newsletter['Newsletter']['html'];
				$this->data['NewsletterSending']['status'] = "build";
				$this->data['NewsletterSending']['date'] = date('Y-m-d H:i:s');
				$this->data['NewsletterSending']['confirm'] = 1;
				$this->data['NewsletterSending']['started'] = 1;
				$this->data['NewsletterSending']['self_sending'] = 1;
				$this->data['NewsletterSending']['wrapper'] = 'share';
				//debug($this->data);
				$allowed = array('sender_name','sender_email','additional_emails','newsletter_id','active','html','status','date','confirm','started','self_sending','wrapper','data');
				if ($this->NewsletterSending->save($this->data,true,$allowed)) {
					$this->Session->setFlash(__d('newsletter','The newsletter sending has been saved', true));
					$this->redirect(array('action' => 'send',$this->NewsletterSending->id));
				} else {
					$this->Session->setFlash(__d('newsletter','The newsletter sending could not be saved. Please, try again.', true));
				}
			}else{
				$this->Session->setFlash(__d('newsletter','Invalid Newsletter', true));
				$this->redirect(array('plugin'=>'newsletter', 'controller'=>'newsletter', 'action'=>'index'));
			}
		}else{
			if(!empty($sended_id)){
				$sended = $this->NewsletterSended->read(null,$sended_id);
				//debug($sended);
				if(!empty($sended['NewsletterSended']['email'])){
					$this->data['NewsletterSending']['sender_email'] = $sended['NewsletterSended']['email'];
				}
				if(!empty($sended['NewsletterEmail']['name'])){
					$this->data['NewsletterSending']['sender_email'] = $sended['NewsletterEmail']['name'];
				}
			}
			$this->data['NewsletterSending']['newsletter_id'] = $newsletter['Newsletter']['id'];
		}
		//debug(App::objects('plugin'));
		if(in_array('O2form',App::objects('plugin'))){
			$this->helpers[] = 'O2form.O2form';
		}
		$this->set('newsletter',$newsletter);
	}
	
	function send($id){
		$sending = $this->NewsletterSending->read(null,$id);
		if(empty($sending) || !$sending['NewsletterSending']['self_sending']){
			$this->Session->setFlash(__d('newsletter','Invalid Newsletter Sending', true));
			$this->redirect(array('plugin'=>'newsletter', 'controller'=>'newsletter', 'action'=>'index'));
		}
		$this->set('sending',$sending);
	}
	
	
	function admin_index() {
		if(!empty($this->params['named']['newsletter'])) {
			$newsletterId = $this->params['named']['newsletter'];
		} 
		if(!empty($newsletterId)){
			$this->paginate['conditions']['NewsletterSending.newsletter_id'] = $newsletterId;
			$newsletter = $this->NewsletterSending->Newsletter->read(null,$newsletterId);
			$this->set('newsletter',$newsletter);
		}
				
		if(!empty($this->params['named']['pending'])) {
			$pending = $this->params['named']['pending'];
		} 	
		if(!empty($pending)){
			$this->paginate['conditions'][] = $this->NewsletterSending->getPendingCond();
			$this->set('pending',$pending);
		}
			
		if(!empty($this->params['named']['scheduled'])) {
			$scheduled = $this->params['named']['scheduled'];
		} 	
		if(!empty($scheduled)){
			$this->paginate['conditions'][] = $this->NewsletterSending->getScheduledCond();
			$this->set('scheduled',$scheduled);
		}
		
		$q = null;
		if(isset($this->params['named']['q']) && strlen(trim($this->params['named']['q'])) > 0) {
			$q = $this->params['named']['q'];
		} elseif(isset($this->data['NewsletterSending']['q']) && strlen(trim($this->data['NewsletterSending']['q'])) > 0) {
			$q = $this->data['NewsletterSending']['q'];
			$this->params['named']['q'] = $q;
		}
		if($q !== null) {
			$this->paginate['conditions']['OR'] = array('NewsletterSending.selected_lists LIKE' => '%'.$q.'%',
														'NewsletterSending.additional_emails LIKE' => '%'.$q.'%',
														'NewsletterSending.html LIKE' => '%'.$q.'%',
														'NewsletterSending.sender_name LIKE' => '%'.$q.'%',
														'NewsletterSending.sender_email LIKE' => '%'.$q.'%',
														'NewsletterSending.data LIKE' => '%'.$q.'%',
														'NewsletterSending.wrapper LIKE' => '%'.$q.'%',
														'NewsletterSending.status LIKE' => '%'.$q.'%',
														'NewsletterSending.console LIKE' => '%'.$q.'%');
		}

		$this->paginate['fields'] = $this->NewsletterSending->minFields();
		
		$this->NewsletterSending->recursive = 0;
		$res = $this->paginate();
		if(!empty($res)){
			$ids = Set::extract('{n}.NewsletterSending.id',$res);
			$findOpt = array(
				'fields'=>array(
					'NewsletterSending.id',
					"COUNT(DISTINCT CASE WHEN `".$this->NewsletterSended->alias."`.`status` IN ('sent','error') THEN `".$this->NewsletterSended->alias."`.`id` END) AS total_sended",
					"COUNT(DISTINCT CASE WHEN `".$this->NewsletterSended->alias."`.`status` = 'error' THEN `".$this->NewsletterSended->alias."`.`id` END) AS errors",
					"COUNT(DISTINCT CASE WHEN `".$this->NewsletterSended->alias."`.`status` IN ('ready','reserved') THEN `".$this->NewsletterSended->alias."`.`id` END) AS remaining"
				),
				'conditions'=>array(
					'NewsletterSending.id' => $ids
				),
				'joins' => array(
					array(
						'alias' => $this->NewsletterSended->alias,
						'table'=> $this->NewsletterSended->useTable,
						'type' => 'left',
						'conditions' => array(
							$this->NewsletterSended->alias.'.sending_id = NewsletterSending.id'
						)
					)
				),
				'group'=>'NewsletterSending.id',
				'recursive'=>-1
			);
			$stats = $this->NewsletterSending->find('all',$findOpt);
			$map = array_flip($ids);
			foreach($stats as $stat){
				$res[$map[$stat['NewsletterSending']['id']]]['NewsletterSending'] = array_merge(
					$res[$map[$stat['NewsletterSending']['id']]]['NewsletterSending'],
					$stat[0]
				);
			}
			//debug($res);
		};
		$this->set('newsletterSendings', $res);
		$this->set('sendlists', $this->NewsletterSendlist->find('list',array('conditions'=>array('NewsletterSendlist.active'=>1))));
	}
	
	function admin_add($newsletter_id = null,$sendlist_id = null) {
		if (!$newsletter_id){
			if (!empty($this->data['NewsletterSending']['newsletter_id'])) {
				$newsletter_id = $this->data['NewsletterSending']['newsletter_id'];
			}elseif (!empty($this->params['named']['newsletter_id'])) {
				$newsletter_id = $this->params['named']['newsletter_id'];
			}else{
				$this->Session->setFlash(__d('newsletter','Invalid Newsletter', true));
				$this->redirect(array('plugin'=>'newsletter', 'controller'=>'newsletter', 'action'=>'index'));
			}
		}
		$newsletter = $this->NewsletterSending->Newsletter->read(null,$newsletter_id);
		if(!$newsletter['Newsletter']['active']){
			$this->Session->setFlash(__d('newsletter','This Newsletter must be active in order to send it.', true));
			$this->redirect(array('plugin'=>'newsletter', 'controller'=>'newsletter', 'action'=>'index'));
		}
		if (!empty($this->data)) {
			$this->NewsletterSending->create();
			if(!empty($newsletter)){
				$this->data['NewsletterSending']['active'] = 1;
				$this->data['NewsletterSending']['html'] = $newsletter['Newsletter']['html'];
				$this->data['NewsletterSending']['status'] = "build";
				if(empty($this->data['NewsletterSending']['scheduled']) || !NewsletterConfig::load('cron')){
					$this->data['NewsletterSending']['date'] = date('Y-m-d H:i:s');
					$this->data['NewsletterSending']['scheduled'] = 0;
				}
				if($newsletter['Newsletter']['tested']){
					$this->data['NewsletterSending']['confirm'] = 1;
				}
				//debug($this->data);
				if ($this->NewsletterSending->save($this->data)) {
					$this->Session->setFlash(__d('newsletter','The newsletter sending has been saved', true));
					if(!empty($this->data['NewsletterSending']['confirm'])){
						if($this->data['NewsletterSending']['scheduled']){
							$this->redirect(array('action' => 'scheduled',$this->NewsletterSending->id));
						}else{
							$this->redirect(array('action' => 'send',$this->NewsletterSending->id));
						}
					}else{
						$this->redirect(array('action' => 'confirm', $this->NewsletterSending->id));
					}
				} else {
					$this->Session->setFlash(__d('newsletter','The newsletter sending could not be saved. Please, try again.', true));
				}
			}else{
				$this->Session->setFlash(__d('newsletter','Invalid Newsletter', true));
				$this->redirect(array('plugin'=>'newsletter', 'controller'=>'newsletter', 'action'=>'index'));
			}
		}else{
			if(empty($sendlist_id)){
				$this->data['NewsletterSending']['selected_lists'] = $newsletter['Newsletter']['TemplateConfig']->getDefaultSendlists($newsletter);
			}
		}
		if ($sendlist_id) {
			$this->data['NewsletterSending']['selected_lists'] = array_merge((array)$this->data['Newsletter']['sendlists'],array($sendlist_id));
		}
		$this->data['NewsletterSending']['newsletter_id'] = $newsletter_id;
		$this->set('sendlists', $this->NewsletterSendlist->find('list',array('conditions'=>array('NewsletterSendlist.active'=>1))));
		
		if(in_array('O2form',App::objects('plugin'))){
			$this->helpers[] = 'O2form.O2form';
		}
		
		$this->set(compact('newsletter'));
	}
	
	function admin_edit($id = null){
		$format = $this->NewsletterSending->getDataSource()->columns['datetime']['format'];
		$sending = $this->NewsletterSending->find('first',array(
			'conditions'=>array(
				'NewsletterSending.id'=>$id,
				$this->NewsletterSending->getScheduledCond(),
				'NewsletterSending.date > ' => date($format)
			)
		));
		if(empty($sending)){
			$this->Session->setFlash(__d('newsletter','Invalid Newsletter Sending', true));
			$this->redirect(array('plugin'=>'newsletter', 'controller'=>'newsletter', 'action'=>'index'));
		}
		
		
		if (!empty($this->data)) {
			if ($this->NewsletterSending->save($this->data)) {
				$this->Session->setFlash(__('The newsletter sending has been saved', true));
				$this->redirect(array('plugin'=>'newsletter', 'controller'=>'newsletter', 'action'=>'index'));
			}else{
				$this->Session->setFlash(__('The newsletter sending could not be saved. Please, try again.', true));
			}
		}else{
			$this->data = $sending;
		}
		
		
		$this->set('sendlists', $this->NewsletterSendlist->find('list',array('conditions'=>array('NewsletterSendlist.active'=>1))));
		
		if(in_array('O2form',App::objects('plugin'))){
			$this->helpers[] = 'O2form.O2form';
		}
		
		$this->set('sending', $sending);
	}
	
	function admin_confirm($id = null){
		if(!$id){
			if(isset($this->params['named']['id']) && is_numeric($this->params['named']['id'])) {
				$id = $this->params['named']['id'];
			}elseif(!empty($this->data['NewsletterSending']['id'])){
				$id = $this->data['NewsletterSending']['id'];
			}
		}
		if($id){
			$sending = $this->NewsletterSending->read(null,$id);
		}
		if(empty($sending)){
			$this->Session->setFlash(__d('newsletter','Invalid Newsletter Sending', true));
			$this->redirect(array('plugin'=>'newsletter', 'controller'=>'newsletter', 'action'=>'index'));
		}
		if (!empty($this->data)) {
			$this->data['NewsletterSending']['html'] = $sending['Newsletter']['html'];
			if ($this->NewsletterSending->save($this->data)) {
				$this->Session->setFlash(__d('newsletter','The newsletter sending has been saved', true));
				if($sending['NewsletterSending']['scheduled']){
					$this->redirect(array('action' => 'scheduled',$this->NewsletterSending->id));
				}else{
					$this->redirect(array('action' => 'send',$this->NewsletterSending->id));
				}
			} else {
				$this->Session->setFlash(__d('newsletter','The newsletter sending could not be saved. Please, try again.', true));
			}
		}
		
		$this->data = $sending;
		if(!empty($this->Auth)){
			$user = $this->Auth->user();
			if(!empty($user['User']['email'])){
				//debug($user);
				$this->data['NewsletterSending']['test_email'] = $user['User']['email'];
			}
		}
		$this->set('newsletterSending',$this->NewsletterSending->read(null,$id));
	}
	
	function admin_test($id = null){
		if(!$id){
			if(isset($this->params['named']['id']) && is_numeric($this->params['named']['id'])) {
				$id = $this->params['named']['id'];
			}elseif(!empty($this->data['NewsletterSending']['id'])){
				$id = $this->data['NewsletterSending']['id'];
			}
		}
		$sending = null;
		if($id){
			$sending = $this->NewsletterSending->read(null,$id);
			$newsletter = $sending;
		}else{
			$newsletter = null;
			if(isset($this->params['named']['newsletter']) && is_numeric($this->params['named']['newsletter'])) {
				$newsletterId = $this->params['named']['newsletter'];
			}elseif(!empty($this->data['NewsletterSending']['newsletter_id'])){
				$newsletterId = $this->data['NewsletterSending']['newsletter_id'];
			}
			if($newsletterId){
				$newsletter = $this->NewsletterSending->Newsletter->read(null,$newsletterId);
			}
		}
		$ajax = ($this->RequestHandler->isAjax() || !empty($this->params['named']['ajax']));
		if(empty($sending) && empty($newsletter)){
			$msg = __d('newsletter','Invalid Newsletter Sending.', true);
			if($ajax){
				$this->autoRender = false;
				echo $msg;
				exit();
			}else{
				$this->Session->setFlash($msg);
				$this->redirect(array('plugin'=>'newsletter', 'controller'=>'newsletter', 'action'=>'index'));
			}
		}
		
		if (!empty($this->data)) {
			if(empty($sending)){
				$sending = array($this->NewsletterSending->alias=>array(
					'additional_emails' => $this->data['NewsletterSending']['test_email'],
					'newsletter_id' => $newsletterId,
					'active' => 1,
					'status' => "test",
					'date' => date('Y-m-d H:i:s'),
				));
				$this->NewsletterSending->save($sending);
				$sending = $this->NewsletterSending->read(null,$this->NewsletterSending->id);
			}
			unset($sending['NewsletterSending']['html']);
			if($this->_sendEmail($this->data['NewsletterSending']['test_email'],$sending)){
				$msg = __d('newsletter','Test email sent.', true).' '.__d('newsletter','Please check your inbox to review the newsletter.', true);
			}else{
				$msg = __d('newsletter','Error, Test email could not be sent.', true);
			}
			$this->NewsletterSending->Newsletter->save(array('id'=>$sending['Newsletter']['id'],'tested'=>1));
			if($ajax){
				$this->autoRender = false;
				echo $msg;
				exit();
			}else{
				$this->Session->setFlash($msg);
				$this->redirect(array('plugin'=>'newsletter', 'controller'=>'newsletter', 'action'=>'index'));
			}
		}else{
			$this->data = $sending;
			if(!empty($this->Auth)){
				$user = $this->Auth->user();
				if(!empty($user['User']['email'])){
					//debug($user);
					$this->data['NewsletterSending']['test_email'] = $user['User']['email'];
				}
			}
		}
		$this->set('newsletterSending',$sending);
		$this->set('newsletter',$newsletter);
	}
	
	function admin_scheduled($id = null){
		$sending = $this->NewsletterSending->find('first',array('conditions'=>array('NewsletterSending.id'=>$id,$this->NewsletterSending->getScheduledCond())));
		if(empty($sending)){
			$this->Session->setFlash(__d('newsletter','Invalid Newsletter Sending', true));
			$this->redirect(array('plugin'=>'newsletter', 'controller'=>'newsletter', 'action'=>'index'));
		}
		$this->set('newsletterSending',$sending);
	}
	
	function admin_send($id = null){
		if(!$id){
			if(isset($this->params['named']['id']) && is_numeric($this->params['named']['id'])) {
				$id = $this->params['named']['id'];
			}elseif(!empty($this->data['NewsletterSending']['id'])){
				$id = $this->data['NewsletterSending']['id'];
			}
		}
		if($id){
			$sending = $this->NewsletterSending->read(null,$id);
		}
		if(empty($sending)){
			$this->Session->setFlash(__d('newsletter','Invalid Newsletter Sending', true));
			$this->redirect(array('plugin'=>'newsletter', 'controller'=>'newsletter', 'action'=>'index'));
		}
		if(!empty($sending['NewsletterSending']['console'])){
			$statistics = $this->_getStats($sending);
			$this->set('statistics',$statistics);
		}
		$this->set('newsletterSending',$sending);
	}
	
	function admin_start($id = null){
		$this->layout = false;
		$this->autoRender = false;
		
		if(!$id){
			if(isset($this->params['named']['id']) && is_numeric($this->params['named']['id'])) {
				$id = $this->params['named']['id'];
			}elseif(!empty($this->data['NewsletterSending']['id'])){
				$id = $this->data['NewsletterSending']['id'];
			}
		}
		if($id){
			$sending = $this->NewsletterSending->read(null,$id);
		}
		
		
		if(empty($sending)){
			$this->_consoleOut(false,
				__d('newsletter','Invalid Newsletter Sending', true),
				array('exit'=>true)
			);
		}
		
		if(empty($sending['NewsletterSending']['confirm'])){
			$this->_consoleOut($id,
				__d('newsletter','Please, you must confirm your Sending first.', true),
				array(
					'exit'=>true,
					'go'=>array('action'=>'admin_confirm')
				)
			);
		}
		
		$this->NewsletterSending->create();
		$data = array('NewsletterSending'=>array('id'=>$id,'started'=>1));//,'active'=>1
		if($this->NewsletterSending->save($data)){
			$this->_consoleOut($id,__('Sending Started',true));
		}else{
			$this->_consoleOut($id,
				__('Could not start Sending',true),
				array('exit'=>true)
			);
		}
		
		$this->_process($id);
		
		
		$this->_console_render();
	}
	
	function admin_pause($id = null){
		if(!$id){
			if(isset($this->params['named']['id']) && is_numeric($this->params['named']['id'])) {
				$id = $this->params['named']['id'];
			}elseif(!empty($this->data['NewsletterSending']['id'])){
				$id = $this->data['NewsletterSending']['id'];
			}
		}
		if($id){
			$sending = $this->NewsletterSending->read(null,$id);
		}
		
		
		if(empty($sending)){
			$this->_consoleOut(false,
				__d('newsletter','Invalid Newsletter Sending', true),
				array('exit'=>true)
			);
		}
		
		$this->NewsletterSending->create();
		$data = array('NewsletterSending'=>array('id'=>$id,'started'=>0));
		if($this->NewsletterSending->save($data)){
			$this->_consoleOut($id,__('Sending Paused.',true));
		}else{
			$this->_consoleOut($id,
				__('Could not start Sending',true),
				array('exit'=>true)
			);
		}
		
		$this->_console_render();
	}
	
	
	function admin_cancel($id = null){
		if(!$id){
			if(isset($this->params['named']['id']) && is_numeric($this->params['named']['id'])) {
				$id = $this->params['named']['id'];
			}elseif(!empty($this->data['NewsletterSending']['id'])){
				$id = $this->data['NewsletterSending']['id'];
			}
		}
		if($id){
			$sending = $this->NewsletterSending->read(null,$id);
		}
		
		
		if(empty($sending)){
			$this->_consoleOut(false,
				__d('newsletter','Invalid Newsletter Sending', true),
				array('exit'=>true)
			);
		}
		
		$this->NewsletterSending->create();
		$data = array('NewsletterSending'=>array('id'=>$id,'active'=>0));
		if($this->NewsletterSending->save($data)){
			$this->_consoleOut($id,__('Sending Canceled.',true));
		}else{
			$this->_consoleOut($id,
				__('Could not start Sending',true),
				array('exit'=>true)
			);
		}
		
		
		$this->_console_render();
	}
	
	function admin_resume($sending){
		$this->layout = false;
		$this->autoRender = false;
		/*$this->_process($sending);
		if(is_numeric($sending)){
			$sending = $this->NewsletterSending->read(null,$sending);
		}
		if(empty($sending)){
			$this->_consoleOut(false,
				__d('newsletter','Invalid Newsletter Sending', true),
				array('exit'=>true)
			);
		}
		$id = $sending['NewsletterSending']['id'];*/
		
		$this->_process($sending);
	}
	
	function cron_tcheck_send(){
		if(NewsletterConfig::load('cron') || NewsletterConfig::load('_cronAuto')){
			$this->layout = false;
			$this->autoRender = false;
			
			Cache::write('newsletter_autocron',1,'cron_cache');
			
			if(!isset($this->params['named']['stream'])){
				$this->params['named']['stream'] = 1;
			}
			
			$format = $this->NewsletterSending->getDataSource()->columns['datetime']['format'];
			$sending = $this->NewsletterSending->find('first',array(
				'conditions'=>array(
					'or'=>array(
						'NewsletterSending.started'=>1,
						array(
							$this->NewsletterSending->getScheduledCond(),
							'NewsletterSending.date <= ' => date($format),
							'status NOT'=>'done'
						)
					),
					'NewsletterSending.active'=>1,
					'Newsletter.active'=>1
				),
				'recursive' => 0,
			));
			if(!empty($sending['NewsletterSending']['scheduled']) && empty($sending['NewsletterSending']['started'])){
				$this->NewsletterSending->create();
				$this->NewsletterSending->save(array(
					'id'=> $sending['NewsletterSending']['id'],
					'started'=> 1,
				));
				$this->log($this->NewsletterSending->data,'wtf');
				$sending['NewsletterSending']['started'] = 1;
			}
			if(!empty($sending)){
				$this->_process($sending);
			}else{
				$this->_consoleOut(null,__('All Sendings are Complete.',true),array('logGeneralMsg'=>false));
			}
			
			$this->_console_render();
			
			//$this->render(false);
		}
	}
	
	function admin_status($id = null){
		if(!$id){
			if(isset($this->params['named']['id']) && is_numeric($this->params['named']['id'])) {
				$id = $this->params['named']['id'];
			}elseif(!empty($this->data['NewsletterSending']['id'])){
				$id = $this->data['NewsletterSending']['id'];
			}
		}
		if($id){
			$this->NewsletterSending->recursive = -1;
			$sending = $this->NewsletterSending->read(null,$id);
		}
		
		if(empty($sending)){
			$this->_consoleOut(false,
				__d('newsletter','Invalid Newsletter Sending', true),
				array('exit'=>true)
			);
		}
		$this->NewsletterSended->recursive = -1;
		$statistics = $this->_getStats($sending);
		$ajax = ($this->RequestHandler->isAjax() || !empty($this->params['named']['ajax']));
		if($ajax){
			$json = array(
				'status'=>$sending['NewsletterSending']['status'],
				'stream'=>in_array($sending['NewsletterSending']['status'],array('build','send')) && $sending['NewsletterSending']['started'] && $sending['NewsletterSending']['active']
			);
			$this->set('json',$json);
		}
		$this->set('ajax',$ajax);
		$this->set('sending',$sending);
		$this->set('statistics',$statistics);
	}
	
	function _getStats($sending){
		$id = $sending['NewsletterSending']['id'];
		$statistics = array(
			'status'=>$sending['NewsletterSending']['status'],
			'last_process_time'=>$sending['NewsletterSending']['last_process_time'],
			'total_sended'=>$this->NewsletterSended->find('count',array('conditions'=>array('sending_id'=>$id,'status'=>array('sent','error')),'recursive'=>-1)),
			'errors'=>$this->NewsletterSended->find('count',array('conditions'=>array('sending_id'=>$id,'status'=>array('error')),'recursive'=>-1))
		);
		if($statistics['status']!='build'){
			$statistics['remaining'] = $this->NewsletterSended->find('count',array('conditions'=>array('sending_id'=>$id,'status'=>array('ready','reserved')),'recursive'=>-1));
			if($statistics['remaining'] == 0){
				$statistics['prc'] = '100 %';
			}else{
				$statistics['prc'] = number_format($statistics['total_sended']/($statistics['total_sended']+$statistics['remaining'])*100,2).' %';
			}
		}else{
			$statistics['remaining'] = $statistics['prc'] = 'Calculating...';
		}
		return $statistics;
	}
	
	
	function _console_render(){
		if(!empty($this->params['named']['stream'])){
			$this->autoRender = false;
		}elseif($this->RequestHandler->isAjax() || !empty($this->params['named']['ajax'])){
			$this->layout = 'ajax';
			$this->set('consoleOut',$this->consoleOut);
			$this->render('ajax_console');
		}elseif(!empty($this->params['named']['no-redirect'])){
			$this->set('consoleOut',$this->consoleOut);
			$this->render('ajax_console');
		}else{
			$this->Session->setFlash($this->consoleOut);
			if(empty($this->consoleGo)){
				$this->redirect(array('plugin'=>'newsletter', 'controller'=>'newsletter', 'action'=>'index'));
			}else{
				$this->redirect($this->consoleGo);
			}
			$this->render(false);
		}
	}
	
	function _process($sending){
		session_write_close();
		ignore_user_abort(true);
		set_time_limit(0);
		$this->NewsletterSending->Behaviors->detach('History');
		if(is_numeric($sending)){
			$this->NewsletterSending->recursive = 0;
			$sending = $this->NewsletterSending->read(null,$sending);
		}
		if(empty($sending['NewsletterSending']['active']) || empty($sending['Newsletter']['active'])){
			$this->_consoleOut(false,
				__d('newsletter','Invalid Newsletter Sending', true),
				array('exit'=>true)
			);
		}
		$id = $sending['NewsletterSending']['id'];
		
		if($sending['NewsletterSending']['status'] == 'done' && !empty($sending['NewsletterSending']['started'])){
			$this->NewsletterSending->create();
			$data = array('NewsletterSending'=>array('id'=>$id,'status'=>'done','started'=>0));
			if($this->NewsletterSending->save($data)){
				$this->_consoleOut($id,__('Sending Complete.',true));
			}else{
				$this->_consoleOut($id,
					__('Could not start Sending',true),
					array('exit'=>true)
				);
			}
		}
		
		if(!$this->_valid_sending($sending)){ return true; }
		if($sending['NewsletterSending']['status'] == 'build'){
			$this->_build($sending);
			
			$sending = $this->NewsletterSending->read(null,$id);
			if(!$this->_valid_sending($sending)){ return true; }
		}
		
		if($sending['NewsletterSending']['status'] == 'render'){
			$this->_render($sending);
			
			$sending = $this->NewsletterSending->read(null,$id);
			if(!$this->_valid_sending($sending)){ return true; }
		}
		
		if($sending['NewsletterSending']['status'] == 'send'){
			$this->_send($sending);
		}
		
		return true;
	}
	
	function _send($sending, $max = null){
		if(is_numeric($sending)){
			$sending = $this->NewsletterSending->read(null,$sending);
		}
		if(empty($sending)){
			$this->_consoleOut(false,
				__d('newsletter','Invalid Newsletter Sending', true),
				array('exit'=>true)
			);
		}
		$id = $sending['NewsletterSending']['id'];
	
		$i = 0;
		$belongsToTmp = $this->NewsletterSended->belongsTo;
		$this->NewsletterSended->recursive = -1;
		$this->NewsletterSended->belongsTo = array();
		$this->NewsletterSended->Behaviors->attach('Containable');
		$continue = $this->_valid_sending($id);
		$done = false;
		
		if($continue){
			//// init sender class ////
			App::import('Lib', 'Newsletter.ClassCollection');
			$senderOpt = NewsletterConfig::load('sender');
			if(!is_array($senderOpt)){
				$senderOpt = array('name' => $senderOpt);
			}
			$sender = ClassCollection::getObject('NewsletterSender',$senderOpt['name']);
			$sender->init($this,$senderOpt);
			
			//// parse global option ////
			$opt = $this->_parseGlobalOpt($sending);
			if(method_exists($sender,'editGlobalOpt')){
				$opt = $sender->editGlobalOpt($opt);
			}
			
			//// get chunk/batch size ////
			$chunk = Configure::read('Newsletter.sending_chunck');
			if(empty($chunk)){
				if(!empty($sender->batchSize)){
					$chunk = $sender->batchSize;
				}else{
					$chunk = 10;
				}
			}
			
			//// get max sending ////
			if(is_null($max)){
				$confMax = NewsletterConfig::load('maxSend');
				if(!is_null($confMax)){
					$max = $confMax;
				}elseif(isset($sender->maxSend)){
					$max = $sender->maxSend;
				}else{
					$max = 10000;
				}
			}
			
			//// get variantes ////
			
			$this->NewsletterSended->belongsTo = $belongsToTmp;
			$this->NewsletterSended->Behaviors->attach('Containable');
			$variants = $this->NewsletterSended->find('all',array(
				'conditions'=>array(
					'NewsletterSended.sending_id' => $id,
				),
				'contain' => array('NewsletterVariant'),
				'group' => 'NewsletterSended.newsletter_variant_id'
			));
			$this->NewsletterSended->belongsTo = array();
			//debug($variants);
			
			reset($variants);
			while ((list($key, $variant) = each($variants)) && $continue) {
				//debug($variant);
				$done = false;
				$opt['variant'] = !empty($variant['NewsletterVariant'])?array('NewsletterVariant'=>$variant['NewsletterVariant']):null;
				if(!empty($opt['variant']['NewsletterVariant']['html'])) {
					$opt['content'] = $opt['variant']['NewsletterVariant']['html'];
				}
				//debug($opt['variant']);
				
				while(($max === false || $i*$chunk<$max) && $continue && !$done){
					$this->NewsletterSended->belongsTo = $belongsToTmp;
					$findOpt = array(
						'conditions'=>array(
							'NewsletterSended.active'=>1,
							'NewsletterSended.status'=>'ready',
							'NewsletterSended.sending_id'=>$id,
							'NewsletterSended.newsletter_variant_id'=>!empty($opt['variant'])?$opt['variant']['NewsletterVariant']['id']:null,
						),
						'limit'=>$chunk,
						'contain'=>'NewsletterEmail'
					);
					$toSend = $this->NewsletterSended->find('all',$findOpt);
					$this->NewsletterSended->belongsTo = array();
					$mailsOptions = array();
					$ids = array();
					//debug($toSend);
					//exit();
					if(!empty($toSend)){
						foreach($toSend as $mail){
							$mailsOptions[$mail['NewsletterSended']['id']] = $this->_parseRecipientOpt($mail,$opt);
							$ids[] = $mail['NewsletterSended']['id'];
						}
						//debug($toSend);
						$this->NewsletterSended->recursive = -1;
						if($this->NewsletterSended->updateAll($this->NewsletterFunct->valFields(array('status'=>'reserved')),array('id'=>$ids,'active'=>1,'status'=>'ready')) && $this->NewsletterSended->getAffectedRows() == count($ids)){
							$this->_sendBatch($sender,$opt,$mailsOptions);
						}else{
							$this->_consoleOut($id,
								__d('newsletter','Could not reserve email, there may be an another process using this sending', true),
								array('exit'=>true)
							);
						}
					}else{
						//sending complete
						
						$done = true;
					}
					$continue = $this->_valid_sending($id);
					$i++;
				}
			}
		}
		
		//=========================== Done ===========================
		if($done){
			$this->NewsletterSending->create();
			$data = array('NewsletterSending'=>array('id'=>$id,'status'=>'done','started'=>0));
			if($this->NewsletterSending->save($data)){
				$this->_consoleOut($id,__('Sending Complete.',true));
			}else{
				$this->_consoleOut($id,
					__('Could not start Sending',true),
					array('exit'=>true)
				);
			}
		}elseif($max && $i*$chunk >= $max){
			$this->_consoleOut($id,__('Hard limit reached.',true));
		}
		
		return true;
	}
	
	function _sendBatch($sender,$opt,$mailsOptions){
		$id = $opt['sending']['NewsletterSending']['id'];
		if(method_exists($sender,'sendBatch')){
			$this->_consoleOut($id,sprintf(__d('newsletter','sending a batch of %s emails...', true),count($mailsOptions)));
			$res = $sender->sendBatch($opt,$mailsOptions);
			$this->NewsletterSended->recursive = -1;
			$okIds = array();
			$errorIds = array();
			if(is_array($res)){
				foreach($res as $mailId => $success){
					if($success){
						$okIds[] = $mailId;
					}else{
						$errorIds[] = $mailId;
					}
				}
			}elseif($res){
				$okIds = array_keys($mailsOptions);
			}else{
				$errorIds = array_keys($mailsOptions);
			}
			if(!empty($okIds)){
				$this->NewsletterSended->updateAll($this->NewsletterFunct->valFields(array('status'=>'sent')),array('id'=>$okIds));
			}
			if(!empty($errorIds)){
				$this->NewsletterSended->updateAll($this->NewsletterFunct->valFields(array('status'=>'error')),array('id'=>$okIds));
			}
			$this->_consoleOut($id,sprintf(__d('newsletter','%s sent, %s errors', true),count($okIds),count($errorIds)));
		}else{
			foreach($mailsOptions as $mailId => $mail){
				$this->_consoleOut($id,sprintf(__d('newsletter','sending to %s...', true),$mail['email']['email']));
				$this->NewsletterSended->create();
				$data = array('id'=>$mailId);
				if($this->_sendSingle($sender,$opt,$mail)){
					$this->_consoleOut($id,__d('newsletter','Done', true),false);
					$data['status'] = 'sent';
				}else{
					$this->_consoleOut($id,__d('newsletter','Error', true),false);
					if(!empty($this->Email->smtpError)){
						$data['error'] = $this->Email->smtpError;
					}
					$data['status'] = 'error';
				}
				$this->NewsletterSended->save($data);
				$this->_updateProcessTime($id,true);
			}
		}
	}
	
	function _build($sending){
		App::import('Lib', 'Newsletter.Sendlist');
		
		if(is_numeric($sending)){
			$sending = $this->NewsletterSending->read(null,$sending);
		}
		if(empty($sending)){
			$this->_consoleOut(false,
				__d('newsletter','Invalid Newsletter Sending', true),
				array('exit'=>true)
			);
		}
		$id = $sending['NewsletterSending']['id'];
		$db =& ConnectionManager::getDataSource($this->NewsletterSended->useDbConfig);
		$this->_updateProcessTime($id);
		
		$this->_consoleOut($id,__d('newsletter','Start Building Sending', true));
		
		//=========================== Data ===========================
		
		$basicInfo = array(
			'active' => 1,
			'status' =>  "ready",
			'sending_id' => $id,
			'newsletter_id' => $sending['NewsletterSending']['newsletter_id'],
			'date' => date('Y-m-d H:i:s'),
			//'name' => null,
			'email' => null
		);
		
		
		
		
		//=========================== Split Dynamic sendlists ===========================
		$sendlists = array();
		$dynSendlists = array();
		if(!empty($sending['NewsletterSending']['selected_lists'])){
			foreach($sending['NewsletterSending']['selected_lists'] as $newsletterSendlist){
				if(Sendlist::isTabled($newsletterSendlist)){
					$dynSendlists[] = $newsletterSendlist;
				}else{
					$sendlists[] = $newsletterSendlist;
				}
			}
		}
		
		//=========================== Grouping ===========================
		$grouping = $sending['Newsletter']['TemplateConfig']->getGrouping($sending);
		if(!empty($grouping)){
			$this->_consoleOut($id,__d('newsletter','Calculating variants', true));
			
			
			$code = sha1(serialize($grouping['fields']));
			$groups = array(
				$code=>array(
					'active' => 1,
					'conditions' => $grouping['fields'],
					'newsletter_id' => $sending['NewsletterSending']['newsletter_id'],
					'code' => $code,
				)
			);
			$listsGroups = array();
			
			if(!empty($sendlists)){
				//--------- normal sendlists ---------
				$findOpt = Sendlist::addSendlistsEmailCond($sendlists,array(
					'fields'=>array('NewsletterEmail.id','NewsletterEmail.name','NewsletterEmail.email'),
					'conditions'=>array(
						'NewsletterEmail.active'=>1
					),
					'group' => 'NewsletterEmail.id',
					'recursive'=>-1,
				));
				
				$findOpt = $this->_groupingBaseFindOpt($grouping,array_keys($this->NewsletterSendlist->NewsletterEmail->schema()));
				if(!empty($findOpt) ){
					$lgroups = $this->NewsletterSendlist->NewsletterEmail->find('all',$findOpt);
					foreach($lgroups as $group){
						$listsGroups['Basic'][] = array(
							'cond' => $group[$this->NewsletterSendlist->NewsletterEmail->alias]
						);
					}
				}
			}
			if(!empty($dynSendlists)){
				//--------- dynamic sendlists ---------
				foreach($dynSendlists as $newsletterSendlist){
					$sendlist = Sendlist::getSendlist($newsletterSendlist);
					$cgrouping = $grouping;
					if($cgrouping['bySendlist']){
						$cgrouping = $sending['Newsletter']['TemplateConfig']->getGrouping($sending,$sendlist);
					}
					$findOpt = $this->_groupingBaseFindOpt($cgrouping,array_keys($sendlist->emailFields()));
					if(!empty($findOpt) ){
						$lgroups = $sendlist->findEmail('all',$findOpt);
						foreach($lgroups as $group){
							$listsGroups[$newsletterSendlist][] = array(
								'cond' => $group[$sendlist->EmailModel->alias]
							);
						}
					}
				}
			}
			
			foreach($listsGroups as &$lgroup){
				foreach($lgroup as &$group){
					$cond = array_merge($cgrouping['fields'],$group['cond']);
					$code = sha1(serialize($cond));
					if(!isset($groups[$code])){
						$groups[$code] = array(
							'active' => 1,
							'conditions' => $cond,
							'newsletter_id' => $sending['NewsletterSending']['newsletter_id'],
							'code' => $code,
						);
					}
					$group['variant'] = &$groups[$code];
				}
			}
			unset($lgroup);
			unset($group);
			
			$this->_consoleOut($id,__d('newsletter','Save variants', true));
			
			$existing = $this->NewsletterVariant->find('list',array('fields'=>array('code','id'),'conditions'=>array('code' => array_keys($groups)),'recursive'=>-1));
			foreach($groups as &$group){
				if(isset($existing[$group['code']])){
					$group['id'] = $existing[$group['code']];
				}else{
					$this->NewsletterVariant->create();
					$this->NewsletterVariant->save($group);
					$group['id'] = $this->NewsletterVariant->id;
				}
			}
			unset($group);
			//debug($listsGroups);
		}
		
		
		//debug($sending);
		$queries = array();
		
		//=========================== Build normal sendlists ===========================
		
		$this->_consoleOut($id,sprintf(__d('newsletter','%s normal Sendlists found', true),count($sendlists)));
		if(!empty($sendlists)){
			$lGroups = !empty($listsGroups['Basic'])?$listsGroups['Basic']:array(null);
			foreach($lGroups as $group){
				//If any sendeded has been added in this same group we assume it the group was allready added by an interucpted build or a parralel process and we skip the query
				$findOpt = array(
					'conditions'=>array(
						'tabledlist_id IS NULL',
						'email_id IS NOT NULL',
						'sending_id' => $id
					),
					'recursive'=>-1,
				);
				if(!empty($group)){
					$findOpt['conditions']['newsletter_variant_id'] = $group['variant']['id'];
				}
				if(!$this->NewsletterSended->find('count',$findOpt)){
					$findOpt = Sendlist::addSendlistsEmailCond($sendlists,array(
						'fields'=>array('NewsletterEmail.id','NewsletterEmail.name','NewsletterEmail.email'),
						'conditions'=>array(
							'NewsletterEmail.active'=>1
						),
						'group' => 'NewsletterEmail.id',
						'recursive'=>-1,
					));
					
					if($sending['NewsletterSending']['check_sended']){
						$findOpt['joins'][] = array(
							'table' => $this->NewsletterSended->useTable,
							'alias' => $this->NewsletterSended->alias,
							'type' => 'left',
							'foreignKey' => false,
							'conditions'=> array(
								$this->NewsletterSendlist->NewsletterEmail->alias.'.id = '.$this->NewsletterSended->alias.'.email_id',
								$this->NewsletterSended->alias.'.newsletter_id' => $sending['NewsletterSending']['newsletter_id']
							)
						);
						$findOpt['conditions'][] = $this->NewsletterSended->alias.'.id IS NULL';
					}
					
					if(!empty($group)){
						$findOpt['conditions'][] = $group['cond'];
						$findOpt['fields']['newsletter_variant_id'] = $group['variant']['id'];
					}
					//debug($findOpt);
					$findOpt['model'] = $this->NewsletterSendlist->NewsletterEmail;
					$queries[] = $findOpt;
					
				}
				
				$this->_updateProcessTime($id,true);
			}
		}
		/*
		foreach($sendlists as $list){
			$this->_consoleOut($id,sprintf(__d('newsletter','Get query for sendlist id : %s', true),$list));
			
			$this->NewsletterSendlist->NewsletterEmail->bindModel(array(
				'hasOne' => array(
					'NewsletterSendlistsEmail' => array(
						'className' => 'Newsletter.NewsletterSendlistsEmail'
					)
				)
			),false);
			$mailsFindOptions = array(
				'fields'=>array('NewsletterEmail.id','NewsletterEmail.name','NewsletterEmail.email'),
				'conditions'=>array('NewsletterSendlistsEmail.newsletter_sendlist_id'=>$list,'NewsletterEmail.active'=>1)
			);
			
			$lGroups = !empty($listsGroups[$list])?$listsGroups[$list]:array(null);
			foreach($lGroups as $group){
				$finalFindOptions = Set::merge($mailsFindOptions,(array)$findOptions);
				if(!empty($group)){
					$finalFindOptions['conditions'][] = $group['cond'];
					$finalFindOptions['fields']['newsletter_variant_id'] = $group['variant']['id'];
				}
				$finalFindOptions['fields']['sendlist_id'] = $list;
				$finalFindOptions['model'] = $this->NewsletterSendlist->NewsletterEmail;
				
				$queries[] = $finalFindOptions;
			}
			
		}*/
		
		//=========================== Build Dynamic sendlists ===========================
		$this->_consoleOut($id,sprintf(__d('newsletter','%s dynamic Sendlists found', true),count($dynSendlists)));
		if(!empty($dynSendlists)){
			$findOptions = array();
			$join = array(
				'table' => $this->NewsletterSended->useTable,
				'alias' => $this->NewsletterSended->alias,
				'type' => 'left',
				'foreignKey' => false,
				'conditions'=> array(
					$this->NewsletterSendlist->NewsletterEmail->alias.'.email = '.$this->NewsletterSended->alias.'.email',
					$this->NewsletterSended->alias.'.newsletter_id' => $sending['NewsletterSending']['newsletter_id']
				)
			);
			if(!$sending['NewsletterSending']['check_sended']){
				$join['conditions'][$this->NewsletterSended->alias.'.sending_id'] = $id;
			}
			$opt = array(
				'joins' => array($join),
				'conditions'=>array(
					$this->NewsletterSended->alias.'.email IS NULL'
				)
			);
			$findOptions = Set::merge($findOptions,(array)$opt);
			
			foreach($dynSendlists as $list){
				$this->_consoleOut($id,sprintf(__d('newsletter','Get query for Dynamic sendlist id : %s', true),$list));
				$tableSendlist = $this->NewsletterFunct->getTableSendlistID($list,true);
				
				
				$lGroups = !empty($listsGroups[$list])?$listsGroups[$list]:array(null);
				foreach($lGroups as $group){
					$finalFindOptions = $findOptions;
					$finalFindOptions['group'] = 'NewsletterEmail.email';
					if(!empty($group)){
						$finalFindOptions['conditions'][] = $group['cond'];
					}
					if($tableSendlist['modelClass']->useDbConfig != $this->NewsletterSended->useDbConfig){
						$finalFindOptions = array();
						if(!empty($group)){
							$finalFindOptions['conditions'][] = $group['cond'];
						}
						$finalFindOptions = $this->NewsletterFunct->tabledEmailGetFindOptions($list,true);
						$finalFindOptions['tableSendlist'] = $tableSendlist;
					}else{
						$finalFindOptions = $this->NewsletterFunct->tabledEmailGetFindOptions($list,true,$finalFindOptions);
					}
					if(!empty($group)){
						$finalFindOptions['fields']['newsletter_variant_id'] = $group['variant']['id'];
					}
					$finalFindOptions['fields']['tabledlist_id'] = $list;
					$queries[] = $finalFindOptions;
					
				}
				
				$this->_updateProcessTime($id,true);
			}
		}
		
		/*if(!empty($grouping)){
			debug($grouping);
		}*/
		/*
		foreach ($queries as $q) {
			$q['model'] = $q['model']->alias;
			debug($q);
		}
		exit();*/
		
		//=========================== Save Queries ===========================
		foreach($queries as $query){
			//--- normalize Queries ---
			$fields = $this->NewsletterFunct->fieldsAddAlias($query['fields']);
			$insertFields = $this->NewsletterSended->tcheckSaveFields(array_keys($fields));
			//debug($insertFields);
			$fields = array_intersect_key($fields,array_flip($insertFields));
			$query['fields'] = $fields;
			if($query['model']->useDbConfig != $this->NewsletterSended->useDbConfig){
				//--------------- external database ---------------
				$this->_consoleOut($id,sprintf(__d('newsletter','The sendlist id : %s Is using an external Database', true),$query['fields']['sendlist_id']));
				$this->_consoleOut($id,sprintf(__d('newsletter','Retrieving data', true),$query['fields']['sendlist_id']));
				$tableSendlist = $query['tableSendlist'];
				//debug($tableSendlist);
				unset($query['tableSendlist']);
				$query['limit'] = 200;
				$i = 0;
				do {
					$query['page'] = $i+1;
					App::import('Lib', 'Newsletter.QueryUtil'); 
					$emails = $query['model']->find('all',QueryUtil::standardizeFindOptions($query));
					if(!empty($emails)){
						$this->_consoleOut($id,sprintf(__d('newsletter','%s Email read', true),count($emails)));
						
						//debug($emails);
						
						App::import('Lib', 'Newsletter.SetMulti');
						$adresses = Set::extract('{n}.'.$query['model']->alias.'.email',$emails);
						//debug($adresses);
						
						//--- get duplicata ---
						$dupFindOpt = array(
							'fields' => array('id','email'),
							'conditions'=>array(
								'email'=>$adresses,
								'newsletter_id'=>$sending['NewsletterSending']['newsletter_id']
							),
							'recursive'=>-1
						);
						if(!$sending['NewsletterSending']['check_sended']){
							$dupFindOpt['conditions']['sending_id'] = $id;
						}
						$duplicata = $this->NewsletterSended->find('list',$dupFindOpt);
						if(!empty($duplicata)){
							$this->_consoleOut($id,sprintf(__d('newsletter','%s dupliqued email Ignored', true),count($duplicata)));
							//debug($duplicata);
						}
						
						
						//--- format data ---
						$toSave = array();
						foreach($emails as $mail){
							$mailData = $this->NewsletterFunct->tabledEmailGetFields($mail,$tableSendlist);
							if(!in_array($mailData['email'],$duplicata)){
								$mailData = array_intersect_key($mailData,array_flip($insertFields));
								$mailData['email_id'] = $mailData['id'];
								unset($mailData['id']);
								$mailData = array_merge($basicInfo,$mailData);
								$toSave[] = $mailData;
							}
						}
						//debug($toSave);
						
						//--- save ---
						if(!empty($toSave)){
							$toSaveSql = array();
							foreach($toSave as $d){
								$toSaveSql[] = "(".implode(",",$this->NewsletterFunct->valFields($d)).")";
							}
							$insertStatement = 'INSERT INTO '.$db->fullTableName($this->NewsletterSended).' (`'.implode("`,`",array_keys($toSave[0])).'`) VALUES '.implode(",",$toSaveSql);
							//debug($insertStatement);
							if($db->execute($insertStatement)){
								$this->_consoleOut($id,sprintf(__d('newsletter','%s saved Emails', true),count($toSave)));
							}
							/*if($this->NewsletterSended->saveAll($toSave)){
								$this->_consoleOut($id,sprintf(__d('newsletter','%s saved Emails', true),count($toSave)));
							}*/
						}
						
						$this->_updateProcessTime($id,true);
					}
					$i++;
					/*if($i>=3){
						$viewClass = $this->view;
						if ($viewClass != 'View') {
							list($plugin, $viewClass) = pluginSplit($viewClass);
							$viewClass = $viewClass . 'View';
							App::import('View', $this->view);
						}
						$View = new $viewClass($this, false);
						echo $View->element('sql_dump');
						
						exit();
					}*/
				} while(!empty($emails));
				/*$this->_consoleOut($id,
					__d('newsletter','External Database lists are not supported yet', true),
					array('exit'=>true)
				);*/
			}else{
				$query['fields']['email_id'] = $query['fields']['id'];
				unset($query['fields']['id']);
				$query['fields'] = array_merge($this->NewsletterFunct->valFields($basicInfo),$query['fields']);
				
				App::import('Lib', 'Newsletter.QueryUtil'); 
				$selectStatement = $db->buildStatement(QueryUtil::standardizeFindOptions($query),$query['model']);
				
				//--- make insert Queries ---
				$fields = $this->NewsletterFunct->fieldsAddAlias($query['fields']);
				$insertFields = $this->NewsletterSended->tcheckSaveFields(array_keys($fields));
				$insertQuery = array(
					'table' => $db->fullTableName($this->NewsletterSended),
					'fields' => array(),
					'select' => $selectStatement
				);
				foreach($insertFields as $f){
					$insertQuery['fields'][] = $db->name($f);
				}
				$insertQuery['fields'] = implode(', ', $insertQuery['fields']);
				$insertStatement = 'INSERT INTO '.$insertQuery['table'].' ('.$insertQuery['fields'].') ('.$insertQuery['select'].')';
				
				$msg = sprintf(__d('newsletter','Execute query for sendlist id : %s', true),$query['fields']['sendlist_id']);
				if(!empty($query['fields']['newsletter_variant_id'])){
					$msg .= ' ('.sprintf(__d('newsletter','Variant id : %s', true),$query['fields']['newsletter_variant_id']).')';
				}
				$this->_consoleOut($id, $msg);
				if($db->execute($insertStatement)){
					$this->_consoleOut($id,sprintf(__d('newsletter','%s saved Emails', true),$this->NewsletterSended->getAffectedRows()));
				}else{
					$this->_consoleOut($id,
						__d('newsletter','Could not save emails', true),
						array('exit'=>true)
					);
				}
				$this->_updateProcessTime($id,true);
			}
		}
		
		
		//=========================== Build Aditionnal Emails ===========================
		if(!empty($sending['NewsletterSending']['additional_emails'])){
			$this->_consoleOut($id,__d('newsletter','Build Aditionnal Emails', true));
			//--- format emails ---
			$emails = explode(',',$sending['NewsletterSending']['additional_emails']);
			$named = "/^<([^>]*)>(.*)$/";
			$add_emails = array();
			foreach($emails as $key => $email){
				$email = array('email'=>trim($email));
				if(preg_match($named,$email['email'],$match)){
					$email['name'] = $match[1];
					$email['email'] = $match[2];
				}
				$email = array_merge($basicInfo,$email);
				$add_emails[$email['email']] = $email;
			}
			
			//--- tcheck for duplicate ---
			$findOpt = array('fields'=>array('DISTINCT email'),'conditions'=>array('email'=>array_keys($add_emails),'newsletter_id'=>$sending['NewsletterSending']['newsletter_id']));
			if(!$sending['NewsletterSending']['check_sended']){
				$findOpt['conditions']['sending_id'] = $id;
			}
			$this->NewsletterSended->recursive = -1;
			$dub = $this->NewsletterSended->find('all',$findOpt);
			if(!empty($dub)){
				$this->_consoleOut($id,sprintf(__d('newsletter','%s dupliqued Email found, they will be ignored', true),count($dub)));
				foreach($dub as $key => $val){
					$dub[$key] = $val['NewsletterSended']['email'];
				}
				$add_emails = array_diff_key($add_emails,array_flip($dub));
			}
			
			//--- save ---
			if(!empty($add_emails)){
				if($this->NewsletterSended->createMany(array_keys($basicInfo),array_values($add_emails))){
					$this->_consoleOut($id,sprintf(__d('newsletter','%s saved Emails', true),$this->NewsletterSended->getAffectedRows()));
				}else{
					$this->_consoleOut($id,
						__d('newsletter','Could not save emails', true),
						array('exit'=>true)
					);
				}
			}else{
				$this->_consoleOut($id,__d('newsletter','No Emails saved', true));
			}
		}
		
		//=========================== Done ===========================
		$this->NewsletterSending->create();
		$data = array('NewsletterSending'=>array('id'=>$id,'status'=>'render'));
		if($this->NewsletterSending->save($data)){
			$this->_consoleOut($id,__('Building Complete.',true));
		}else{
			$this->_consoleOut($id,
				__('Could not start Sending',true),
				array('exit'=>true)
			);
		}
		
		return true;
	}
	
	function _groupingBaseFindOpt($grouping,$myFields){
		$groupingFields = array_keys($grouping['fields']);
		$groupingFields = array_values(array_intersect($myFields,$groupingFields));
		$missing = count($groupingFields) != count($grouping['fields']);
		if(!empty($groupingFields) && (!$missing || !$grouping['withMissing']['disable']) ){
			//debug($groupingFields);
			$findOpt = array('fields'=>$groupingFields,'group'=>$groupingFields,'active'=>true);
			if(!$missing || $grouping['withMissing']['validate']){
				$findOpt['conditions'] = $grouping['validation'];
			}
			return $findOpt;
		}
		return null;
	}
	
	function _render($sending){
	
		if(is_numeric($sending)){
			$sending = $this->NewsletterSending->read(null,$sending);
		}
	
		if(empty($sending)){
			$this->_consoleOut(false,
				__d('newsletter','Invalid Newsletter Sending', true),
				array('exit'=>true)
			);
		}
		$id = $sending['NewsletterSending']['id'];
		
		$this->NewsletterSended->Behaviors->attach('Containable');
		$unrenderedVariants = $this->NewsletterSended->find('all',array(
			'conditions'=>array(
				'NewsletterSended.sending_id' => $id, 
				'NewsletterSended.newsletter_variant_id IS NOT NULL',
				'NewsletterVariant.html IS NULL'
			),
			'contain' => array('NewsletterVariant'),
			'group' => 'NewsletterSended.newsletter_variant_id'
		));
		
		if(empty($sending['Newsletter']['html'])){
			$this->_updateProcessTime($id,true);
			$this->_consoleOut($id,__('Rendering the newsletter',true));
			$this->NewsletterFunct->renderNewsletter($sending);
		}
		
		
		$i = 0;
		foreach($unrenderedVariants as $variant){
			$this->_updateProcessTime($id,true);
			$this->_consoleOut($id,sprintf(__('Render variant %d/%d',true), $i+1, count($unrenderedVariants)));
			$this->NewsletterFunct->renderNewsletter($sending,true,$variant);
			$i++;
			//debug($variant['NewsletterVariant']['html']);
			//debug($variant);
		}
		
		
		$this->NewsletterSending->create();
		$data = array('NewsletterSending'=>array('id'=>$id,'status'=>'send'));
		if($this->NewsletterSending->save($data)){
			$this->_consoleOut($id,__('Render Complete.',true));
		}else{
			$this->_consoleOut($id,
				__('Could not start Sending',true),
				array('exit'=>true)
			);
		}
		
		return true;
	}
	
	
	function _valid_sending($sending){
		if(is_numeric($sending)){
			$sending = $this->NewsletterSending->read(null,$sending);
		}
		if(empty($sending)){
			$this->_consoleOut(false,
				__d('newsletter','Invalid Newsletter Sending', true),
				array('exit'=>true)
			);
		}
		$id = $sending['NewsletterSending']['id'];
		
		//debug(date("Y-m-d H:i:s u",strtotime($sending['NewsletterSending']['last_process_time'])));
		
		
		
		if($sending['NewsletterSending']['status'] == 'done'){
			$this->_consoleOut($id,__d('newsletter','Sending Complete.', true));
			return false;
		}
		
		if(empty($sending['NewsletterSending']['active'])){
			$this->_consoleOut($id,__d('newsletter','Sending Canceled.', true));
			return false;
		}
		
		if(empty($sending['NewsletterSending']['started'])){
			$this->_consoleOut($id,__d('newsletter','Sending Paused.', true));
			return false;
		}
		
		if(!empty($this->lastProcessTime) && $this->lastProcessTime != strtotime($sending['NewsletterSending']['last_process_time'])){
			$this->_consoleOut($id,__d('newsletter','An another process using this sending has been detected.', true));
			return false;
		}
		
		return true;
	}
	
	function _updateProcessTime($id,$sleep=false){
		if($sleep){
			$speed = Configure::read('Newsletter.send_speed');
			if(empty($speed)){
				$speed = 1000;// * 2000
			}
			usleep($speed);
		}
		$data = array('id'=>$id,'last_process_time'=>date('Y-m-d H:i:s'));
		if($this->NewsletterSending->save($data)){
			$this->lastProcessTime = strtotime($data['last_process_time']);
		}else{
			$this->_consoleOut($id,
				__('Could not Update process time for Newsletter Sending',true),
				array('exit'=>true)
			);
		}
	}
	
	function _consoleOut($id,$out = array(),$options=array()){
		$defaultOptions = array(
			'lineBreak' => true,
			'exit' => false,
			'go' => null,
			'logGeneralMsg' => true
		);
		if(is_null($options)){
			$options = array();
		}elseif(!is_array($options)){
			$options = array('lineBreak'=>$options);
		}
		$options = array_merge($defaultOptions,$options);
		
		$out = (array)$out;
		if(!empty($options['go'])){
			$out[] = '<a href="'.Router::url($options['go'],true).'">'.Router::url($options['go'],true).'</a>';
			$this->consoleGo = $options['go'];
		}
		
		
		if(!empty($id) && is_numeric($id) && $id > 0){
			$console = $this->NewsletterSending->read('console',$id);
			$console = explode("\n",$console['NewsletterSending']['console']);
			if(!empty($console) && !$options['lineBreak']){
				$appenLines = array(implode(' ',array_merge(array(array_pop($console)),(array)$out)));
			}else{
				$appenLines = (array)$out;
			}
			
			$console = array_merge($console,$appenLines);
			$console_max = Configure::read('Newsletter.console_max_length');
			if(empty($console_max)){
				$console_max = 100;
			}
			while(count($console)>$console_max){
				array_shift($console);
			}
			$data = array('id'=>$id,'console'=>implode("\n",$console));
			if($this->NewsletterSending->save($data)){
			}else{
				$this->_consoleOut(false,
					__('Could not Update console content for Newsletter Sending',true),
					array('exit'=>true)
				);
			}
		}elseif($options['logGeneralMsg']){
			$this->log($out,LOG_DEBUG);
		}
		
		$out = implode(($options['lineBreak']?"<br>\n":' '),(array)$out);
		if(!empty($this->consoleOut)){
			$out = ($options['lineBreak']?"<br>\n":' ') . $out;
		}
		if(!empty($out)){
			$this->consoleOut.=$out;
			if(!empty($this->params['named']['stream'])){
				echo $out;
				@ob_flush();
				flush();
			}
		}
		if(!empty($options['exit'])){
			$this->_console_render();
			exit();
		}
	}
	
	function _parseGlobalOpt($sending,$newsletter=null){
		$opt = array();
		
		//// get data ////
		if(is_numeric($sending)){
			$sending = $this->NewsletterSending->read(null,$sending);
		}
		if(empty($newsletter)){
			if(!empty($sending['Newsletter'])){
				$newsletter = $sending;
			}elseif(!empty($sending['NewsletterSending']['newsletter_id'])){
				$newsletter = $sending['NewsletterSending']['newsletter_id'];
			}
		}
		if(is_numeric($newsletter)){
			$newsletter = $this->NewsletterSending->Newsletter->read(null,$newsletter);
		}
		$opt['content'] = '';
		if(!empty($sending['NewsletterSending']['html'])){
			$opt['content']  = $sending['NewsletterSending']['html'];
		}elseif(!empty($newsletter['Newsletter']['html'])){
			$opt['content']  = $newsletter['Newsletter']['html'];
		}
		$opt['sending'] = $sending;
		$opt['newsletter'] = $newsletter;
		
		
		//// get sender (from) ////
		if(!empty($sending['NewsletterSending']['sender_email'])){
			$opt['from'] = $sending['NewsletterSending']['sender_email'];
			if(!empty($sending['NewsletterSending']['sender_name'])){
				$opt['from'] = $sending['NewsletterSending']['sender_name'].' <'.$sending['NewsletterSending']['sender_email'].'>';
			}
		}elseif(!empty($newsletter['Newsletter']['sender'])){
			$opt['from'] = $newsletter['Newsletter']['sender'];
		}elseif(Configure::read('Newsletter.sendEmail')){
			$opt['from'] = Configure::read('Newsletter.sendEmail');
			if(is_array($opt['from'])){
				$opt['from'] = reset($opt['from']);
			}
		}else{
			$opt['from'] = $this->EmailUtils->defaultEmail();
		}
		
		//// get replyTo ////
		if(Configure::read('Newsletter.replyTo')){
			$opt['replyTo'] = Configure::read('Newsletter.replyTo');
		}else{
			$opt['replyTo'] = $opt['from'] ;
		}
		
		//// get errorReturn ////
		if(Configure::read('Newsletter.errorReturn')){
			$opt['return'] = Configure::read('Newsletter.errorReturn');
		}
		
		//// get subject ////
		$opt['subject'] = $newsletter['Newsletter']['title'];
		
		//// wrapper ////
		if(!empty($sending['NewsletterSending']['wrapper'])){
			$opt['content'] = $this->_renderWrapper($sending['NewsletterSending']['wrapper'],$opt);
		}
		
		return $opt;
	}
	
	function _parseRecipientOpt($email,$globalOpt){
		$opt = array();
		
		//// get data ////
		if(empty($email)){
			return false;
		}
		if(!is_array($email)){
			$email = array('email'=>$email);
		}
		$sended_id = 0;
		if(!empty($email['NewsletterSended'])){
			$fullData = $email;
			$email = $email['NewsletterSended'];
			if(!empty($fullData['NewsletterEmail'])){
				$email = array_merge($fullData['NewsletterEmail'],$email);
			}
			$email['sended_id'] = $email['id'];
			unset($email['id']);
		}
		if(!empty($email['sended_id'])){
			$sended_id = $email['sended_id'];
		}
		$opt['email'] = $email;
		$opt['sended_id'] = $sended_id;
		
		
		//// get recipient (to) ////
		if(isset($email['name']) && $email['name']){
			$opt['to'] = $email['name'].' <'.$email['email'].'>';
			$recipient_name = $email['name'];
		}else{
			$opt['to'] = $email['email'];
			$recipient_name = __d('newsletter','Mister/Miss',true);
		}
		
		//// Replace content placeholders ////
		$opt['replace'] = array(
			'%sended_id%' => $sended_id,
			'%recipient_name%' => $recipient_name,
			'%recipient_email%' => $email['email'],
		);
		if(isset($email['data'])){
			preg_match_all('/%mdata\:([\w.]+)%/', $globalOpt['content'], $matches, PREG_SET_ORDER);
			foreach($matches as $matche){
				$path = explode('.',$matche[1]);
				if(count($path)==1){
					array_unshift($path,'NewsletterEmail');//mettre table alias pour les tabled sendlist
				}
				$val = $email['data'];
				foreach($path as $n){
					if(isset($val[$n])){
						$val = $val[$n];
					}else{
						$val = '';
						break;
					}
				}
				$opt['replace'][$matche[0]] = $val;
			}
		}
		
		return $opt;
	}
	
	function _renderWrapper($wrapper,$opt){
		$viewClass = $this->view;

		if ($viewClass != 'View') {
			list($plugin, $viewClass) = pluginSplit($viewClass);
			$viewClass = $viewClass . 'View';
			App::import('View', $this->view);
		}

		$View = new $viewClass($this, false);
		$View->layout = false;
		
		$opt['newsletterContent'] = $opt['content'];
		
		return $View->element('email' . DS . 'html' . DS . $wrapper, $opt, true);
	}
	
	function _sendEmail($email,$sending,$newsletter=null){
		
		//// init sender class ////
		App::import('Lib', 'Newsletter.ClassCollection');
		$senderOpt = NewsletterConfig::load('sender');
		if(!is_array($senderOpt)){
			$senderOpt = array('name' => $senderOpt);
		}
		$sender = ClassCollection::getObject('NewsletterSender',$senderOpt['name']);
		$sender->init($this,$senderOpt);
		
		//// parse global option ////
		$opt = $this->_parseGlobalOpt($sending,$newsletter);
		if(method_exists($sender,'editGlobalOpt')){
			$opt = $sender->editGlobalOpt($opt);
		}
		
		
		$sender = ClassCollection::getObject('NewsletterSender',$senderOpt['name']);
		$sender->init($this,$senderOpt);
		
		$mailOpt = $this->_parseRecipientOpt($email,$opt);
	
		return $this->_sendSingle($sender,$opt,$mailOpt);
	}
	
	function _sendSingle($sender,$opt,$mailOpt = array()){
		if(method_exists($sender,'send')){
			return $sender->send(array_merge($opt,$mailOpt));
		}elseif(method_exists($sender,'sendBatch')){
			return $sender->sendBatch($opt,array($mailOpt));
		}else{
			return false;
		}
	}

}
?>