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
				if(empty($this->data['NewsletterEmail']['sendlist_id'])){
					if(Configure::read('Newsletter.defaultSendlist')){
						$this->data['NewsletterEmail']['sendlist_id'] = Configure::read('Newsletter.defaultSendlist');
					}elseif(!empty($sendlists)){
						if(count($sendlists) == 1){
							$this->data['NewsletterEmail']['sendlist_id'] = key($sendlists);
						}else{
							$this->Session->setFlash(__d('newsletter','You must choose at least one sendlist.', true));
							$error = true;
						}
					}else{
						$this->data['NewsletterEmail']['sendlist_id'] = 1;
					}
				}elseif(!empty($sendlists) && count(array_diff((array)$this->data['NewsletterEmail']['sendlist_id'],array_keys($sendlists)))){
					$this->Session->setFlash(__d('newsletter','Invalid sendlist.', true));
					$error = true;
				}
				
				
				$lists = (array)$this->data['NewsletterEmail']['sendlist_id'];
				if(!$error){
					$exists = $this->NewsletterEmail->find('list', array(
						'fields'=>array('id','sendlist_id'),
						'conditions'=>array(
							'email'=>$this->data['NewsletterEmail']['email'],
							'sendlist_id'=>$this->data['NewsletterEmail']['sendlist_id'],
							'active'=>1
						),
						'recursive'=>-1
					));
					if($exists){
						$lists = array_diff($lists,$exists);
					}
					if(!count($lists)){
						$this->Session->setFlash(__d('newsletter','Ce email est déjà présent dans notre base de données.', true));
						$error = true;
					}
				}
				if(!$error){
					$this->data['NewsletterEmail']['active'] = 1;
					
					$ids = array();
					foreach($lists as $list){
						$data = $this->data['NewsletterEmail'];
						$data['sendlist_id'] = $list;
						
						$this->NewsletterEmail->create();
						$this->NewsletterEmail->save($data);
						$ids[] = $this->NewsletterEmail->id;
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

	function admin_index($listId = null) {
		$q = null;
		if(isset($this->data['q']) && !empty($this->data['q'])) {
			$q = $this->data['q'];
			$this->params['named']['q'] = $this->data['q'];
		}
		elseif(isset($this->params['named']['q']) && !empty($this->params['named']['q'])) {
			$q = $this->params['named']['q'];
		}
		
		if($q != null) {
			$this->paginate['conditions'] = array('OR' => array(
				Inflector::singularize($this->name) . '.email LIKE' => '%'.$q.'%',
				Inflector::singularize($this->name) . '.name LIKE' => '%'.$q.'%'
			));
		}
		
		if(!$listId && !empty($this->params['named']['id'])){
			$listId = $this->params['named']['id'];
		}
		if($listId){
			$this->NewsletterEmail->recursive = 0;
			App::import('Lib', 'Newsletter.Sendlist');
			if(Sendlist::isTabled($listId)){
				$tableSendlist = $this->NewsletterFunct->getTableSendlistID($listId,true);
				$Model = $tableSendlist['modelClass'];
				$modelName = $Model->alias;
				$findOptions = $this->NewsletterFunct->tabledEmailGetFindOptions($tableSendlist,!$tableSendlist['showInnactive']);
				if($q != null) {
					$this->paginate['conditions'] = array('OR' => array(
						'email LIKE' => '%'.$q.'%'
					));
					if($tableSendlist['firstNameField'] && $Model->hasField($tableSendlist['firstNameField'])){
						$this->paginate['conditions']['OR']['first_name LIKE'] = '%'.$q.'%';
					}
					if($tableSendlist['lastNameField'] && $Model->hasField($tableSendlist['lastNameField'])){
						$this->paginate['conditions']['OR']['last_name LIKE'] = '%'.$q.'%';
					}
					if($tableSendlist['nameField'] && $Model->hasField($tableSendlist['nameField'])){
						$this->paginate['conditions']['OR']['name LIKE'] = '%'.$q.'%';
					}
				}
				$this->paginate = array_merge_recursive($this->paginate,$findOptions);
				$Model->recursive = -1;
				$mails = $this->paginate($Model);
				$newsletterEmails = array();
				foreach($mails as $mail){
					$newsletterEmails[] = $this->NewsletterFunct->tabledEmailGetFields($mail,$tableSendlist,'NewsletterEmail');
				}
				$toRender = 'tabled_email';
			}else{
				$newsletterEmails = $this->paginate(null,array('NewsletterEmail.sendlist_id'=>$listId));
			}
			//debug($newsletterEmails);
			$this->set('sendlist', $this->NewsletterEmail->NewsletterSendlist->read(null, $listId));
		}else{
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

}
?>