<?php
class NewsletterSendingsController extends NewsletterAppController {

	var $name = 'NewsletterSendings';
	
	var $uses = array('Newsletter.NewsletterSending','Newsletter.NewsletterSendlist','Newsletter.NewsletterSended');
	var $components = array('Email','Newsletter.EmailUtils', 'Newsletter.Funct', 'RequestHandler');
	
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
				unset($this->data['NewsletterSending']['selected_lists']);
				$this->data['NewsletterSending']['active'] = 1;
				$this->data['NewsletterSending']['html'] = $newsletter['Newsletter']['html'];
				$this->data['NewsletterSending']['status'] = "build";
				$this->data['NewsletterSending']['date'] = date('Y-m-d H:i:s');
				$this->data['NewsletterSending']['confirm'] = 1;
				$this->data['NewsletterSending']['started'] = 1;
				$this->data['NewsletterSending']['self_sending'] = 1;
				$this->data['NewsletterSending']['wrapper'] = 'share';
				//debug($this->data);
				if ($this->NewsletterSending->save($this->data)) {
					$this->Session->setFlash(__('The newsletter sending has been saved', true));
					$this->redirect(array('action' => 'send',$this->NewsletterSending->id));
				} else {
					$this->Session->setFlash(__('The newsletter sending could not be saved. Please, try again.', true));
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
				$this->data['NewsletterSending']['date'] = date('Y-m-d H:i:s');
				//debug($this->data);
				if ($this->NewsletterSending->save($this->data)) {
					$this->Session->setFlash(__('The newsletter sending has been saved', true));
					$this->redirect(array('action' => 'confirm',$this->NewsletterSending->id));
				} else {
					$this->Session->setFlash(__('The newsletter sending could not be saved. Please, try again.', true));
				}
			}else{
				$this->Session->setFlash(__d('newsletter','Invalid Newsletter', true));
				$this->redirect(array('plugin'=>'newsletter', 'controller'=>'newsletter', 'action'=>'index'));
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
		
		$this->set(compact('newsletters'));
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
				$this->Session->setFlash(__('The newsletter sending has been saved', true));
				$this->redirect(array('action' => 'send',$this->NewsletterSending->id));
			} else {
				$this->Session->setFlash(__('The newsletter sending could not be saved. Please, try again.', true));
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
		if($id){
			$sending = $this->NewsletterSending->read(null,$id);
		}
		$ajax = ($this->RequestHandler->isAjax() || !empty($this->params['named']['ajax']));
		if(empty($sending)){
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
			unset($sending['NewsletterSending']['html']);
			if($this->_sendEmail($this->data['NewsletterSending']['test_email'],$sending)){
				$msg = __d('newsletter','Test email sent.', true);
			}else{
				$msg = __d('newsletter','Error, Test email could not be sent.', true);
			}
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
		$this->set('newsletterSending',$sending);
	}
	
	function admin_start($id = null){
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
		
		$this->resume($id);
		
		
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
	
	function resume($sending){
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
		$this->layout = false;
		$this->autoRender = false;
		if(!isset($this->params['named']['stream'])){
			$this->params['named']['stream'] = 1;
		}
		
		$this->NewsletterSending->recursive = -1;
		$sending = $this->NewsletterSending->find('first',array('conditions'=>array('started'=>1,'active'=>1)));
		if(!empty($sending)){
			$this->resume($sending);
		}else{
			$this->_consoleOut(null,__('All Sendings are Complete.',true),array('logGeneralMsg'=>false));
		}
		
		$this->_console_render();
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
		$statistics = array(
			'status'=>$sending['NewsletterSending']['status'],
			'last_process_time'=>$sending['NewsletterSending']['last_process_time'],
			'total_sended'=>$this->NewsletterSended->find('count',array('conditions'=>array('sending_id'=>$id,'status'=>array('sent','error')))),
			'errors'=>$this->NewsletterSended->find('count',array('conditions'=>array('sending_id'=>$id,'status'=>array('error'))))
		);
		if($statistics['status']!='build'){
			$statistics['remaining'] = $this->NewsletterSended->find('count',array('conditions'=>array('sending_id'=>$id,'status'=>array('ready','reserved'))));
			if($statistics['remaining'] == 0){
				$statistics['prc'] = '100 %';
			}else{
				$statistics['prc'] = number_format($statistics['total_sended']/($statistics['total_sended']+$statistics['remaining'])*100,2).' %';
			}
		}else{
			$statistics['remaining'] = $statistics['prc'] = 'Calculating...';
		}
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
			$sending = $this->NewsletterSending->read(null,$sending);
		}
		if(empty($sending)){
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
		
		if($sending['NewsletterSending']['status'] == 'send'){
			$this->_send($sending);
		}
		
		return true;
	}
	
	function _send($sending, $max = 1000){
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
	
		if(!$max){
			$max = 1000;
		}
		$i = 0;
		$chunk = Configure::read('Newsletter.sending_chunck');
		if(empty($chunk)){
			$chunk = 10;
		}
		$this->NewsletterSended->recursive = -1;
		$this->NewsletterSended->belongsTo = array();
		$continue = $this->_valid_sending($id);
		$done = false;
		while($i<$max && $continue){
			$toSend = $this->NewsletterSended->find('all',array('conditions'=>array('active'=>1,'status'=>'ready','sending_id'=>$id),'limit'=>$chunk));
			$ids = array();
			if(!empty($toSend)){
				foreach($toSend as $mail){
					$ids[] = $mail['NewsletterSended']['id'];
				}
				//debug($toSend);
				$this->NewsletterSended->recursive = -1;
				
				if($this->NewsletterSended->updateAll($this->Funct->valFields(array('status'=>'reserved')),array('id'=>$ids,'active'=>1,'status'=>'ready')) && $this->NewsletterSended->getAffectedRows() == count($ids)){
					foreach($toSend as $mail){
						$this->_consoleOut($id,sprintf(__d('newsletter','sending to %s...', true),$mail['NewsletterSended']['email']));
						$this->NewsletterSended->create();
						$data = array('id'=>$mail['NewsletterSended']['id']);
						if($this->_sendEmail($mail,$sending)){
							$this->_consoleOut($id,__d('newsletter','Done', true),null,false);
							$data['status'] = 'sent';
						}else{
							$this->_consoleOut($id,__d('newsletter','Error', true),null,false);
							if(!empty($this->Email->smtpError)){
								$data['error'] = $this->Email->smtpError;
							}
							$data['status'] = 'error';
						}
						$this->NewsletterSended->save($data);
						$this->_updateProcessTime($id,true);
					}
					//$this->NewsletterSended->updateAll($this->Funct->valFields(array('status'=>'ready')),array('id'=>$ids));
				}else{
					$this->_consoleOut($id,
						__d('newsletter','Could not reserve email, there may be an another process using this sending', true),
						array('exit'=>true)
					);
				}
			}else{
				//sending complete
				
				$done = true;
				$continue = false;
			}
			$continue = $this->_valid_sending($id);
			$i++;
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
		}
		
		return true;
	}
	
	function _build($sending){
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
		/*if(isset($this->data['Newsletter']['conditions'])){
			$opt = array('conditions'=>(array)$this->data['Newsletter']['conditions']);
			$findOptions = Set::merge($findOptions,(array)$opt);
		}*/
		
		//debug($sending);
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
		//debug($sending);
		$queries = array();
		//=========================== Build Dynamic sendlists ===========================
		//--- Get Dynamic sendlists ---
		$sendlists = array();
		$dynSendlists = array();
		if(!empty($sending['NewsletterSending']['selected_lists'])){
			foreach($sending['NewsletterSending']['selected_lists'] as $newsletterSendlist){
				if($this->Funct->isTableSendlist($newsletterSendlist)){
					$dynSendlists[] = $newsletterSendlist;
				}else{
					$sendlists[] = $newsletterSendlist;
				}
			}
		}
		//--- Get Queries ---
		$this->_consoleOut($id,sprintf(__d('newsletter','%s Dynamic Sendlists found', true),count($dynSendlists)));
		foreach($dynSendlists as $list){
			$this->_consoleOut($id,sprintf(__d('newsletter','Get query for Dynamic sendlist id : %s', true),$list));
			$tableSendlist = $this->Funct->getTableSendlistID($list,true);
			if($tableSendlist['modelClass']->useDbConfig != $this->NewsletterSended->useDbConfig){
				$finalFindOptions = $this->Funct->tabledEmailGetFindOptions($list,true);
				$finalFindOptions['tableSendlist'] = $tableSendlist;
			}else{
				$finalFindOptions = $this->Funct->tabledEmailGetFindOptions($list,true,$findOptions);
			}
			$finalFindOptions['fields']['sendlist_id'] = $list;
			$queries[] = $finalFindOptions;
			
			$this->_updateProcessTime($id,true);
		}
		
		$this->_consoleOut($id,sprintf(__d('newsletter','%s normal Sendlists found', true),count($sendlists)));
		//=========================== Build normal sendlists ===========================
		
		foreach($sendlists as $list){
			$this->_consoleOut($id,sprintf(__d('newsletter','Get query for sendlist id : %s', true),$list));
			$mailsFindOptions = array('fields'=>array('NewsletterEmail.id','NewsletterEmail.name','NewsletterEmail.email'), 'conditions'=>array('NewsletterEmail.sendlist_id'=>$list,'NewsletterEmail.active'=>1));
			$finalFindOptions = Set::merge($mailsFindOptions,(array)$findOptions);
			$finalFindOptions['fields']['sendlist_id'] = $list;
			$finalFindOptions['model'] = $this->NewsletterSendlist->NewsletterEmail;
			$queries[] = $finalFindOptions;
		}
		
		//=========================== Save Queries ===========================
		foreach($queries as $query){
			//--- normalize Queries ---
			$fields = $this->Funct->fieldsAddAlias($query['fields']);
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
					$emails = $query['model']->find('all',$this->Funct->standardizeFindOptions($query));
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
							$mailData = $this->Funct->tabledEmailGetFields($mail,$tableSendlist);
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
								$toSaveSql[] = "(".implode(",",$this->Funct->valFields($d)).")";
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
				$query['fields'] = array_merge($this->Funct->valFields($basicInfo),$query['fields']);
				
				$selectStatement = $db->buildStatement($this->Funct->standardizeFindOptions($query),$query['model']);
				
				//--- make insert Queries ---
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
				
				$this->_consoleOut($id,sprintf(__d('newsletter','Execute query for sendlist id : %s', true),$query['fields']['sendlist_id']));
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
		
		//=========================== Done ===========================
		$this->NewsletterSending->create();
		$data = array('NewsletterSending'=>array('id'=>$id,'status'=>'send'));
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
	
	function _sendEmail($email,$sending,$newsletter=null){
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
		$content = '';
		if(!empty($sending['NewsletterSending']['html'])){
			$content = $sending['NewsletterSending']['html'];
		}elseif(!empty($sending['Newsletter']['html'])){
			$content = $sending['Newsletter']['html'];
		}
		
		if(empty($email) || empty($content)){
			return false;
		}
		if(!is_array($email)){
			$email = array('email'=>$email);
		}
		$sended_id = 0;
		if(!empty($email['NewsletterSended'])){
			$email = $email['NewsletterSended'];
			$email['sended_id'] = $email['id'];
			unset($email['id']);
		}
		if(!empty($email['sended_id'])){
			$sended_id = $email['sended_id'];
		}
		
		$this->Email->reset();
		$smtpOptions = Configure::read('Newsletter.smtpOptions');
		if(!empty($smtpOptions)){
			//debug($smtpOptions);
			$this->Email->smtpOptions = $smtpOptions;
			$this->Email->delivery = 'smtp';
		}
		$this->Email->lineLength = 1000;
		if(isset($email['name']) && $email['name']){
			$this->Email->to = $email['name'].' <'.$email['email'].'>';
			$recipient_name = $email['name'];
		}else{
			$this->Email->to = $email['email'];
			$recipient_name = __d('newsletter','Mister/Miss',true);
		}
		$this->Email->subject = $newsletter['Newsletter']['title'];
		if(Configure::read('Newsletter.replyTo')){
			$this->Email->replyTo = Configure::read('Newsletter.replyTo');
		}elseif(Configure::read('Newsletter.sendEmail')){
			$this->Email->replyTo = Configure::read('Newsletter.sendEmail');
		}else{
			$this->Email->replyTo = $this->EmailUtils->defaultEmail();
		}
		if(!empty($sending['NewsletterSending']['sender_email'])){
			$this->Email->from = $sending['NewsletterSending']['sender_email'];
			if(!empty($sending['NewsletterSending']['sender_name'])){
				$this->Email->from = $sending['NewsletterSending']['sender_name'].' <'.$sending['NewsletterSending']['sender_email'].'>';
			}
		}else{
			if(Configure::read('Newsletter.sendEmail')){
				$this->Email->from = Configure::read('Newsletter.sendEmail');
			}else{
				$this->Email->from = $this->EmailUtils->defaultEmail();
			}
		}
		if(Configure::read('Newsletter.errorReturn')){
			$this->Email->return = Configure::read('Newsletter.errorReturn');
		}
		$this->Email->sendAs = 'html';
		//$this->Email->template = 'newsletter';
		//$this->Email->delivery = 'debug';
		$cur_content = $content;
		$cur_content = str_replace('%sended_id%',$sended_id,$cur_content);
		$cur_content = str_replace('%recipient_name%',$recipient_name,$cur_content);
		$cur_content = str_replace('%recipient_email%',$email['email'],$cur_content);
		$cur_content = str_replace('%recipient_email%',$email['email'],$cur_content);
		
		if(isset($email['data'])){
			preg_match_all('/%mdata\:([\w.]+)%/', $cur_content, $matches, PREG_SET_ORDER);
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
				$cur_content = str_replace($matche[0],$val,$cur_content);
			}
		}
		
		if(!empty($sending['NewsletterSending']['wrapper'])){
			$this->Email->template = $sending['NewsletterSending']['wrapper'];
			$this->set(compact('email','sending','newsletter'));
			$this->set('newsletterContent',$cur_content);
			return $this->Email->send();
		}else{
			return $this->Email->send($cur_content);
		}
	}

}
?>