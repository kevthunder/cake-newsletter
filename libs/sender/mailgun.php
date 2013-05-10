<?php
class MailgunNewsletterSender extends NewsletterSender {

	var $url = 'https://api.mailgun.net/v2/';
	var $user = 'o2web.mailgun.org';
	var $key = '198yqxluossf7sicp7skiag3tpjlmif5';

	var $batchSize = 500;
	var $maxSend = '200000';
	
	function editGlobalOpt($opt){
		if(empty($opt['newsletter']['Newsletter']['external_key'])){
			$ch = $this->_initReq('campaigns');
			
			$toPost['html'] = $opt['content'];
			//$toPost['html'] = '<p>ceci est un test</p>';
			
			$name = 'ID:'.$opt['newsletter']['Newsletter']['id'].' on '.Router::url('/',true);
			if(strlen($name) > 64){
				$name = substr($name,0,64);
			}
			$toPost = array('name'=>$name);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $toPost);
			
			$result = $this->_sendReq($ch);
			debug($result);
			
			if(!empty($result['campaign']['id'])){
				$Newsletter = $this->controller->NewsletterSending->Newsletter;
				$Newsletter->create();
				$Newsletter->save(array(
					'id'=>$opt['newsletter']['Newsletter']['id'],
					'external_key'=>$result['campaign']['id']
				));
			}
			
		}
		return $opt;
	}
	
	function sendBatch($opt,$mailsOptions){
		
		$ch = $this->_initReq('messages');

		$forward = array('from','subject');
		$toPost = array_intersect_key($opt,array_flip($forward));
		//$toPost['from'] = "gtq <info@o2web.ca>";
		
		$replaceMap = array();
		$recipientVars = array();
		$to = array();
		foreach($mailsOptions as $mailId => $mailOpt){
			$vars = array();
			if(!empty($mailOpt['replace'])){
				foreach($mailOpt['replace'] as $key => $val){
					$nKey = trim(Inflector::slug($key),'_');
					$replaceMap[$key] = '%recipient.'.$nKey.'%';
					$vars[$nKey] = $val;
				}
			}
			$recipientVars[$mailOpt['email']['email']] = $vars;
			$to[] = $mailOpt['to'];
		}
		if(!empty($recipientVars)){
			$toPost['recipient-variables'] = json_encode($recipientVars);
			$toPost['to'] = implode(', ',$to);
		}
		if(!empty($replaceMap)){
			$opt['content'] = str_replace(array_keys($replaceMap),array_values($replaceMap),$opt['content']);
		}
		if(!empty($opt['newsletter']['Newsletter']['external_key'])){
			$toPost['o:campaign'] = $opt['newsletter']['Newsletter']['external_key'];
		}
		$toPost['html'] = $opt['content'];
		//$toPost['html'] = '<p>ceci est un test</p>';
		
		//debug(h(var_export($toPost,true)));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $toPost);
		


		/*
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD, 'api:key-198yqxluossf7sicp7skiag3tpjlmif5');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_URL, 'https://api.mailgun.net/v2/o2web.mailgun.org/messages');
		curl_setopt($ch, CURLOPT_POSTFIELDS, array('from' => "gtq <info@o2web.ca>",
												 'to' => 'kgiguere@o2web.ca',
												 'subject' => 'test',
												 'text' => 'test',
												 'html' => '<p>ceci est un test</p>'
		));
		*/
		
		$result = $this->_sendReq($ch);
		
		
		return !empty($result['id']);
	}
	
	function _initReq($funct,$mode='POST'){
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD, 'api:key-'.$this->key);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
		if($mode != 'GET'){
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		}
		curl_setopt($ch, CURLOPT_URL, $this->url.$this->user.'/'.$funct);
		
		return $ch;
	}
	
	function _sendReq($req){
	
		$result = curl_exec($req);
		
		//$info = curl_getinfo($req);
		//debug($info);
			
		curl_close($req);

		$result = json_decode($result,true);
		//debug($result);
		
		return $result;
	}
	
	function beforeStats($controller,$newsletter,$queries){
		if(!empty($newsletter['Newsletter']['external_key'])){
		
			$this->syncEvent($newsletter,'bounced','bounce');
			
			
			$queries["bounces"] = array(
				'conditions'=>array(
					'NewsletterSended.newsletter_id'=>$newsletter['Newsletter']['id'],
					'NewsletterStat.action' => 'bounce',
				),
				'model'=>'NewsletterStat',
				'type' => 'count'
			);
			
			$this->syncEvent($newsletter,'opened',array('localEvent' => 'mailgun-view' /*,'full'=>true,'startPage'=>30*/));
			
			$queries['uniqueviews']['conditions'] = $queries['allviews']['conditions'] = array(
				'NewsletterSended.newsletter_id' => $newsletter['Newsletter']['id'],
				'NewsletterStat.action' => 'mailgun-view'
			);
			
			//debug($queries);
			
			return $queries;
		}
	}
	
	function beforeGraph($controller,$newsletter,$query){
	
		$query['conditions'] = $queries['allviews']['conditions'] = array(
			'NewsletterSended.newsletter_id' => $newsletter['Newsletter']['id'],
			'NewsletterStat.action' => 'mailgun-view'
		);
		
		return $query;
	}
	
	function syncEvent($newsletter,$event,$options = array()){
		$defOpt = array(
			'localEvent' => $event,
			'full' => false,
			'startPage' => 1,
		);
		if(!is_array($options)) $options = array('localEvent' =>$options);
		$opt = array_merge($defOpt,$options);
		
		$NewsletterStat = ClassRegistry::init('Newsletter.NewsletterStat');
		$NewsletterSended = ClassRegistry::init('Newsletter.NewsletterSended');
		
		$campaign_id = $newsletter['Newsletter']['external_key'];
		$go = true;
		$last = $NewsletterStat->find('first',array('conditions'=>array('NewsletterStat.action'=>$opt['localEvent'],'NewsletterSended.newsletter_id'=>$newsletter['Newsletter']['id']),'order'=>'NewsletterStat.date DESC'));
		if(!empty($last)){
			$lTime = strtotime($last['NewsletterStat']['date']);
		}
		$format = $NewsletterStat->getDataSource()->columns['datetime']['format'];
		$i = $opt['startPage'];
		
		while($go){
			$req = $this->_initReq('campaigns/'.$campaign_id.'/events?event='.$event.'&page='.$i,'GET');
			$result = $this->_sendReq($req);
			//debug($result);
			if(!empty($result)){
				foreach($result as $r){
					$rTime = strtotime($r['timestamp']);
					if($opt['full'] || empty($last) || $rTime>=$lTime){
						if(!empty($last) && $rTime <= $lTime){
							$exists = $NewsletterStat->find('first',array(
								'conditions'=>array(
									'NewsletterStat.action'=>$opt['localEvent'],
									'NewsletterSended.newsletter_id'=>$newsletter['Newsletter']['id'],
									'LOWER(`NewsletterSended`.`email`)'=>$r['recipient'],
									'NewsletterStat.date' => date($format,$rTime),
								)
							));
							if(!empty($exists)){
								if($opt['full']){
									continue;
								}else{
									$go = false;
									break;
								}
							}
						}
						$sended = $NewsletterSended->find('first',array(
							'conditions'=>array(
								'NewsletterSended.newsletter_id'=>$newsletter['Newsletter']['id'],
								'LOWER(`NewsletterSended`.`email`)'=>$r['recipient'],
							),
							'order'=>'NewsletterSended.date DESC'
						));
						//debug($sended);
						if(!empty($sended)){
							$NewsletterStat->create();
							$data = array(
								 'sended_id' => $sended['NewsletterSended']['id'],
								 'date' => date($format,$rTime),
								 'action' => $opt['localEvent']
							);
							if(!empty($r['ip'])){
								$data['ip_address'] = $r['ip'];
							}
							$NewsletterStat->save($data);
						}
					}else{
						$go = false;
						break;
					}
				}
			}else{
				$go = false;
			}
			$i++;
		}
		
	}
	
	
}