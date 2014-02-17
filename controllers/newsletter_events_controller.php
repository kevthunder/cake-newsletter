<?php
class NewsletterEventsController extends NewsletterAppController {

	var $name = 'NewsletterEvents';

	function admin_index() {
		$q = null;
		if(isset($this->params['named']['q']) && strlen(trim($this->params['named']['q'])) > 0) {
			$q = $this->params['named']['q'];
		} elseif(isset($this->data['NewsletterEvent']['q']) && strlen(trim($this->data['NewsletterEvent']['q'])) > 0) {
			$q = $this->data['NewsletterEvent']['q'];
			$this->params['named']['q'] = $q;
		}
					
		if($q !== null) {
			$this->paginate['conditions']['OR'] = array('NewsletterEvent.action LIKE' => '%'.$q.'%',
														'NewsletterEvent.url LIKE' => '%'.$q.'%',
														'NewsletterEvent.ip_address LIKE' => '%'.$q.'%',
														'NewsletterEvent.user_agent LIKE' => '%'.$q.'%');
		}
		
		if(!empty($this->params['named']['e'])) {
			$this->paginate['conditions']['NewsletterEvent.action'] = $this->params['named']['e'];
		}
		if(!empty($this->params['named']['newsletter'])) {
			$this->paginate['conditions']['NewsletterSended.newsletter_id'] = $this->params['named']['newsletter'];
		}

		$this->NewsletterEvent->recursive = 0;
		$this->set('newsletterEvents', $this->paginate());
	}
	
}
?>