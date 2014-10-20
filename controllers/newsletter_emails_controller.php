<?php
class NewsletterEmailsController extends NewsletterAppController {

	var $name = 'NewsletterEmails';
	var $helpers = array('Html', 'Form');
	var $components = array('Email','Newsletter.NewsletterFunct','Newsletter.EmailUtils');

	function index() {
		$this->redirect('add');
	}
	/*
	function view($id = null) {
		if (!$id) {
			$this->Session->setFlash(__('Invalid NewsletterEmail.', true));
			$this->redirect(array('action'=>'index'));
		}
		$this->set('newsletterEmail', $this->NewsletterEmail->read(null, $id));
	}*/
	
	function add() {
		$sendlists = $this->NewsletterEmail->NewsletterSendlist->find('list',array(
			'conditions'=>array(
				'NewsletterSendlist.subscriptable'=>1
			),
			'order'=>array('NewsletterSendlist.order ASC','NewsletterSendlist.title ASC'),
			'recursive' => -1,
		));
		if (!empty($this->data)) {
			$error = false;
			if(!empty($this->data['NewsletterEmail']['email']) && strpos($this->data['NewsletterEmail']['email'], '@') !== false){
				if(!isset($this->data['NewsletterEmail']['name']) && isset($this->data['NewsletterEmail']['first_name']) && isset($this->data['NewsletterEmail']['last_name'])){
					$this->data['NewsletterEmail']['name'] = $this->data['NewsletterEmail']['first_name'] .' '.$this->data['NewsletterEmail']['last_name'];
				}
	
				$this->NewsletterEmail->create();
				if(!empty($this->data['NewsletterEmail']['sendlist_id'])){
					$this->data['NewsletterSendlist'] = (array)$this->data['NewsletterEmail']['sendlist_id'];
				}
				if(empty($this->data['NewsletterSendlist'])){
					if(Configure::read('Newsletter.defaultSendlist')){
						$this->data['NewsletterSendlist'] = (array)Configure::read('Newsletter.defaultSendlist');
					}elseif(!empty($sendlists)){
						if(count($sendlists) == 1){
							$this->data['NewsletterSendlist'] = array(key($sendlists));
						}else{
							$this->Session->setFlash(__d('newsletter','You must choose at least one sendlist.', true));
							$error = true;
						}
					}else{
						$this->data['NewsletterSendlist'] = array(1);
					}
				}elseif(!empty($sendlists) && count(array_diff((array)$this->data['NewsletterSendlist'],array_keys($sendlists)))){
					$this->Session->setFlash(__d('newsletter','Invalid sendlist.', true));
					$error = true;
				}
				
				
				if(!$error){
					if($this->NewsletterEmail->save($this->data)){
						if(!empty($this->NewsletterEmail->data['NewsletterEmail']['existed'])){
							$this->Session->setFlash(__d('newsletter','This email allready exists in our database. Your informations has been updated.', true));
						}
					}else{
						$this->Session->setFlash(__d('newsletter','The email could not be saved. Please, try again.', true));
						$error = true;
					}
				}
				if(!$error){
					
					$confirmEmail = Configure::read('Newsletter.ConfirmEmail');
					if(!empty($confirmEmail)){
						$this->data['NewsletterEmail']['id'] = $this->NewsletterEmail->id;
						$this->_send_confirm_email($this->data);
					}
					if(isset($this->data['NewsletterEmail']['redirect']) && $this->data['NewsletterEmail']['redirect']) {
						if($this->data['NewsletterEmail']['redirect'] == 'back'){
							$this->redirect($this->referer());
						}elseif($this->data['NewsletterEmail']['redirect'] == 'confirm'){
							$this->Session->delete('newsletterEmailId');
							$this->Session->write('newsletterEmailId',$this->NewsletterEmail->id);
							//debug('here');
							//debug($this->NewsletterEmail->id);
							$this->redirect('confirm');
						}else{
							$this->redirect($this->data['NewsletterEmail']['redirect']);
						}
					}elseif(Configure::read('Newsletter.EmailAdd.confirm')){
						$this->Session->delete('newsletterEmailId');
							//debug('here2');
							//debug($this->NewsletterEmail->id);
						$this->Session->write('newsletterEmailId',$this->NewsletterEmail->id);
						$this->redirect('confirm');
					}else{
						$this->redirect('/');
					}
				}
			}else{
				$this->Session->setFlash(__d('newsletter','Invalid email.', true));
				$error = true;
			}
			if($error && isset($this->data['NewsletterEmail']['invalid_redirect']) && $this->data['NewsletterEmail']['invalid_redirect']) {
				if($this->data['NewsletterEmail']['invalid_redirect'] == 'back'){
					$this->redirect($this->referer());
				}else{
					$this->redirect($this->data['NewsletterEmail']['invalid_redirect']);
				}
			}
		}
		$this->set("sendlists",$sendlists);
	}
	
	function _send_confirm_email($data){
		///////////// confirmation email /////////////
		$this->Email->reset();
		$this->Email->to = $data['NewsletterEmail']['email'];
		if(Configure::read('Newsletter.ConfirmEmail.subject')){
			$this->Email->subject = Configure::read('Newsletter.ConfirmEmail.subject');
		}else{
			$this->Email->subject = __d('newsletter','Newsletter Subcription confirmation',true);
		}
		if(Configure::read('Newsletter.ConfirmEmail.sender')){
			$this->Email->from = Configure::read('Newsletter.ConfirmEmail.sender');
		}else if(Configure::read('Newsletter.sendEmail')){
			$sender = Configure::read('Newsletter.sendEmail');
			if(is_array($sender)){
				$sender = reset($sender);
			}
			$this->Email->from = $sender;
		}else{
			$this->Email->from = $this->EmailUtils->defaultEmail();
		}
		if(Configure::read('Newsletter.ConfirmEmail.replyTo')){
			$this->Email->replyTo = Configure::read('Newsletter.ConfirmEmail.replyTo');
		}else if(Configure::read('Newsletter.ConfirmEmail.sender')){
			$this->Email->replyTo = Configure::read('Newsletter.ConfirmEmail.sender');
		}else if(Configure::read('Newsletter.replyTo')){
			$this->Email->replyTo = Configure::read('Newsletter.replyTo');
		}else if(Configure::read('Newsletter.sendEmail')){
			$sender = Configure::read('Newsletter.sendEmail');
			if(is_array($sender)){
				$sender = reset($sender);
			}
			$this->Email->replyTo = $sender;
		}else{
			$this->Email->replyTo = $this->EmailUtils->defaultEmail();
		}
		$this->Email->template = 'confirm';
		if(Configure::read('Newsletter.ConfirmEmail.sendAs')){
			$this->Email->sendAs = Configure::read('Newsletter.ConfirmEmail.sendAs');
		}else{
			$this->Email->sendAs = 'both'; // because we like to send pretty mail
		}
		$this->set('newsletterEmail', $data);
		if(!$this->Email->send()){
			$this->Session->setFlash(__d('newsletter','Email confirmation could not be sent.', true));
			return false;
		}else{
			return true;
		}
	}
	
	function confirm() {
		$id = $this->Session->read('newsletterEmailId');
		//debug($id);
		$this->Session->delete('newsletterEmailId');
		if (!$id) {
			$this->Session->setFlash(__d('newsletter','Invalid NewsletterEmail.', true));
			$this->redirect('/');
		}
		$this->set('newsletterEmail', $this->NewsletterEmail->read(null, $id));
	}
	
	function reenable($code = null){
		$this->NewsletterSended = ClassRegistry::init('Newsletter.NewsletterSended');
		$this->NewsletterSended->Behaviors->attach('Containable');
		if(!empty($code)){
			$sended = $this->NewsletterSended->byCode($code,array('contain'=>array('Newsletter'),'recursive'=>0));
		}
		
		$test = false;
		if(
			empty($sended)
			&& isset($this->user['User']['id']) 
			&& is_numeric($this->user['User']['id']) 
			&& $this->Acl->check(array('model' => 'User', 'foreign_key' => $this->user['User']['id']), 'admin')
			&& !empty($this->params['named']['newsletter_id'])
		) {
			$test = $this->params['named']['newsletter_id'];
			$this->NewsletterSended->Newsletter->checkActive = false;
			$sended = $this->NewsletterSended->Newsletter->read(null, $this->params['named']['newsletter_id']);
		}
		
		if(!$test){
			if(empty($sended)){
				$this->Session->setFlash(__d('newsletter','Email Could not be found.', true));
				$this->redirect('add');
				return;
			}
			
			App::import('Lib', 'Newsletter.Sendlist');
			Sendlist::enable_email($sended['NewsletterSended']['email']);
		}
		$this->set('sended', $sended);
		$this->set('test', $test);
	}

	function admin_index($listId = null) {
		$q = null;
		if(isset($this->data['q']) && !empty($this->data['q'])) {
			$q = $this->data['q'];
			$this->params['named']['q'] = $this->data['q'];
		}
		elseif(isset($this->params['named']['q']) && !empty($this->params['named']['q'])) {
			$q = $this->params['named']['q'];
		}
		
		
		if(!$listId && !empty($this->params['named']['id'])){
			$listId = $this->params['named']['id'];
		}
		if($listId){
			App::import('Lib', 'Newsletter.Sendlist');
			$sendlist = Sendlist::getSendlist($listId);
			
			$findOptions = array('search'=>$q);
			$findOptions = $sendlist->emailQuery($findOptions,false);
			//debug($findOptions);
			$this->paginate = $findOptions;
			$mails = $this->paginate($sendlist->EmailModel);
			//debug($mails);
			$newsletterEmails = $sendlist->parseResult($mails,array('alias'=>'NewsletterEmail','local'=>true));
			//debug($newsletterEmails);
			if($sendlist->type == 'tabled'){
				$this->set('fields', $sendlist->emailFields());
				$toRender = 'tabled_email';
			}
			$this->set('sendlist', $sendlist->getInfo());
		}else{
		
			if($q != null) {
				$this->paginate['conditions'] = array('OR' => array(
					Inflector::singularize($this->name) . '.email LIKE' => '%'.$q.'%',
					Inflector::singularize($this->name) . '.name LIKE' => '%'.$q.'%'
				));
			}
		
			$this->NewsletterEmail->recursive = 0;
			$newsletterEmails = $this->paginate();
		}
		$this->set('newsletterEmails', $newsletterEmails);
		if(isset($toRender)){
			$this->render('admin_'.$toRender);
		}
	}

	function admin_view($id = null) {
		if (!$id) {
			$this->Session->setFlash(__d('newsletter','Invalid NewsletterEmail.', true));
			$this->redirect(array('action'=>'index'));
		}
		$this->set('newsletterEmail', $this->NewsletterEmail->read(null, $id));
	}

	function admin_add() {
		if (!empty($this->data)) {
			$this->NewsletterEmail->create();
			if ($this->NewsletterEmail->save($this->data)) {
				$this->Session->setFlash(__d('newsletter','The NewsletterEmail has been saved', true));
				$action = array('action'=>'index');
				if(isset($this->params['named']['list_id'])){
					$action[] = $this->params['named']['list_id'];
				}
				$this->redirect($action);
			} else {
				$this->Session->setFlash(__d('newsletter','The NewsletterEmail could not be saved. Please, try again.', true));
			}
		}
		if(isset($this->params['named']['list_id'])){
			$this->data = array();
			$this->data['NewsletterEmail']['sendlist_id'] = $this->params['named']['list_id'];
			$this->set('cur_list_id',$this->params['named']['list_id']);
		}
		$this->set("sendlists",$this->NewsletterEmail->NewsletterSendlist->find('list'));
	}

	function admin_edit($id = null) {
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(__('Invalid NewsletterEmail', true));
			$this->redirect(array('action'=>'index'));
		}
		if (!empty($this->data)) {
			if ($this->NewsletterEmail->save($this->data)) {
				$this->Session->setFlash(__d('newsletter','The NewsletterEmail has been saved', true));
				$action = array('action'=>'index');
				if(isset($this->params['named']['list_id'])){
					$action[] = $this->params['named']['list_id'];
				}
				$this->redirect($action);
			} else {
				$this->Session->setFlash(__d('newsletter','The NewsletterEmail could not be saved. Please, try again.', true));
			}
		}
		if (empty($this->data)) {
			$this->data = $this->NewsletterEmail->read(null, $id);
		}
		if(isset($this->params['named']['list_id'])){
			$this->set('cur_list_id',$this->params['named']['list_id']);
		}
		$this->set("sendlists",$this->NewsletterEmail->NewsletterSendlist->find('list'));
	}

	function admin_delete($id = null) {
		if (!$id) {
			$this->Session->setFlash(__d('newsletter','Invalid id for NewsletterEmail', true));
			$action = array('action'=>'index');
			if(isset($this->params['named']['list_id'])){
				$action[] = $this->params['named']['list_id'];
			}
			$this->redirect($action);
		}
		if ($this->NewsletterEmail->delete($id)) {
			$this->Session->setFlash(__d('newsletter','NewsletterEmail deleted', true));
			$action = array('action'=>'index');
			if(isset($this->params['named']['list_id'])){
				$action[] = $this->params['named']['list_id'];
			}
			$this->redirect($action);
		}
	}
	
	function admin_update_bounces(){
		$this->NewsletterFunct->updateBounces();
		$this->render(false);
	}

}
?>