<?php
App::import('Vendor', 'Newsletter.browscap',array('file'=>'browscap.php'));
App::import('Vendor', 'Newsletter.php-ofc-library', array('file'=>'php-ofc-library/open-flash-chart.php'));
App::import('Vendor', 'Newsletter.PHPExcel',array('file' => 'PHPExcel/IOFactory.php'));

class NewsletterController extends NewsletterAppController {

	var $name = 'Newsletter';
	var $helpers = array('Html', 'Form', 'Newsletter.NewsletterMaker', 'Javascript');
	var $uses = array('Newsletter.Newsletter','Newsletter.NewsletterBox','Newsletter.NewsletterSendlist','Newsletter.NewsletterEmail','Newsletter.NewsletterSended','Newsletter.NewsletterStat');
	var $components = array('Email','Newsletter.Funct', 'RequestHandler', 'Session');
	
	function index() {
		$this->paginate['order'] = 'date DESC';
		$this->set('newsletters', $this->paginate());
	}

	function view($id = null) {
		//$this->autoLayout = false;
		$this->layout = "empty";
		if (!$id) {
			$this->Session->setFlash(__d('newsletter','Invalid Newsletter.', true));
			debug('Invalid Newsletter.');
			$this->redirect(array('action'=>'index'));
		}
		$Newsletter = $this->Newsletter->read(null, $id);
		$this->set('Newsletter', $Newsletter);
	}
	function redir($url=null,$sended_id=null){
		$this->autoRender = false;
		//$url = str_replace(array('>',';','!'),array('/',':','?'),$url);
		/*while(strpos($url,urlencode('/'))!== false || strpos($url,urlencode('%'))!== false){
			$url = urldecode($url);
		}*/
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
		
		if($sended_id){
			$this->NewsletterStat->create();
			$visite = array();
			$visite['sended_id'] = $sended_id;
			$visite['date'] = date('Y-m-d H:i:s');
			$visite['action'] = 'click';
			$visite['url'] = $url;
			$visite['ip_address'] = $_SERVER['REMOTE_ADDR'];
			$visite['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
			$this->NewsletterStat->save($visite);
		}
		//debug($this->params);
		if(!preg_match('/http[s]?\:\/\//',$url)){
			$url= $url."?utm_source=newsletter&utm_medium=email&utm_campaign=email";
		}
		if($url){
			$this->redirect($url);
		}
	}
	function counter($sended_id=null,$img_url=null){
		//Configure::write('debug', 1);
		if($sended_id){
			$this->NewsletterStat->create();
			$visite = array();
			$visite['sended_id'] = $sended_id;
			$visite['date'] = date('Y-m-d H:i:s');
			$visite['action'] = 'view';
			$visite['ip_address'] = $_SERVER['REMOTE_ADDR'];
			$visite['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
			$this->NewsletterStat->save($visite);
		}
		if($img_url){
			$img_path = WWW_ROOT . str_replace('>',DS,$img_url);
		}else{
			$img_path = APP . 'plugins'. DS .'newsletter'. DS .'webroot'. DS .'img'. DS .'blank.gif';
		}
		if(file_exists($img_path)){
			$path_parts = pathinfo($img_path);
			debug($path_parts);
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
	function add_email($send_list_id,$email,$name=null) {
		if($send_list_id && $email){
			$this->NewsletterEmail->create();
			$email_data = array();
			$email_data['active'] = 1;
			$email_data['name'] = $name;
			$email_data['email'] = $email;
			$email_data['sendlist_id'] = $send_list_id;
			if($this->NewsletterEmail->save($email_data)){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	function unsubscribe($sended_id=null) {
		//Configure::write('debug', 1);
		//$view = 'unsubscribe_step1';
		if(!empty($this->data) && isset($this->data['NewsletterEmail']['confirm'])){
			if($this->data['NewsletterEmail']['confirm']){
				if($this->data['NewsletterEmail']['email']){
					$email_data = array();
					//$email_data['id'] = $this->data['NewsletterEmail']['id'];
					$email_data['active'] = '0';
					$count = $this->NewsletterEmail->updateAll($email_data, array('email'=>$this->data['NewsletterEmail']['email']));
					$tableSendlists = $this->Funct->getTableSendlists(true);
					foreach($tableSendlists as $tableSendlist){
						if($tableSendlist['allowUnsubscribe']){
							$Model = $tableSendlist['modelClass'];
							$modelName = $Model->alias;
							if($Model->hasField($tableSendlist['activeField'])){
								$count += $Model->updateAll(array($tableSendlist['activeField']=>0), array($modelName.'.'.$tableSendlist['emailField']=>$this->data['NewsletterEmail']['email']));
							}
						}
					}
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
				$email_id = $sended['NewsletterSended']['email_id'];
				$str_email = $sended['NewsletterSended']['email'];
			}
			if($str_email || $email_id){
				if($email_id){
					$email = $this->NewsletterEmail->read(null,$email_id);
				}else{
					$email = $this->NewsletterEmail->find('first', array('conditions'=>array('email'=>$str_email),'order'=>array('active DESC')));
					if(empty($email) || !$email['NewsletterEmail']['active']){
						$tabledEmail = $this->Funct->getTabledEmail($str_email);
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
		$this->set('newsletters', $res);
		$this->set('sendlists', $this->NewsletterSendlist->find('all',array('conditions'=>array('NewsletterSendlist.active'=>1),'recursive'=>-1)));
	}
	function admin_view($id = null) {
		//$this->autoLayout = false;
		$this->layout = "empty";
		if (!$id) {
			$this->Session->setFlash(__d('newsletter','Invalid Newsletter.', true));
			debug('Invalid Newsletter.');
			$this->redirect(array('action'=>'index'));
		}
		$Newsletter = $this->Newsletter->read(null, $id);
		$this->set('Newsletter', $Newsletter);
	}
	function admin_graphs($id = null) {
		//Configure::write('debug', 0);
		//$this->layout = null;
		// generate some random data
		srand((double)microtime()*1000000);
		$title = new title( __d("newsletter","Views per time",true));
		$chart = new open_flash_chart();
		$chart->set_title($title);
		
		
		$newsletter = $this->Newsletter->read(null, $id);
		
		
		// Views
		$dates = array();
		$values = array();
		
		
		//// init sender class ////
		App::import('Lib', 'Newsletter.ClassCollection');
		$senderOpt = NewsletterConfig::load('sender');
		if(!is_array($senderOpt)){
			$senderOpt = array('name' => $senderOpt);
		}
		$sender = ClassCollection::getObject('NewsletterSender',$senderOpt['name']);
		
		//// query ////
		$query = array(
			'fields' => array('count(*) as nb','DATE(NewsletterStat.date) as date'),
			'conditions'=>array(
				'NewsletterSended.newsletter_id'=>$id,
				'or' => array(
					'NewsletterStat.action' => 'view',
					array(
						'NewsletterStat.action IS NULL',
						'NewsletterStat.url' => null,
					)
				)
			),
			'group' => 'DATE(NewsletterStat.date)',
			'order' => 'DATE(NewsletterStat.date)',
			'model'=>'NewsletterStat',
		);
		
		//// beforeGraph callback ////
		if(method_exists($sender,'beforeGraph')){
			$res = $sender->beforeGraph($this,$newsletter,$query);
			if(!empty($res) ){
				$query = $res;
			}
		}
		
		//// Execute Query ////
		
		//$sql = "select count(*) as nb,DATE(NewsletterStats.date) as date from newsletter_stats NewsletterStats left join newsletter_sended NewsletterSended on  NewsletterSended.id = NewsletterStats.sended_id where NewsletterSended.newsletter_id = '".$id."' AND url is NULL AND (`action` IS NULL or `action` = 'view') group by DATE(NewsletterStats.date) order by DATE(NewsletterStats.date)";
		//$views = $this->NewsletterSended->query($sql);
		
		//debug($query);
		$views = $this->_stat_query($query);
		//debug($views);
		
		$min_value = 99999999;
		$max_value = 0;
		foreach($views as $view){
			$dates[$view[0]["date"]] = $view[0]["nb"];
			if($view[0]["nb"] < $min_value){
				$min_value = $view[0]["nb"];
			}
			if($view[0]["nb"] > $max_value){
				$max_value = $view[0]["nb"];
			}
		}
		if($max_value == $min_value){
			$max_value++;
			$min_value--;
		}
		$min_value = 0;
		//pr($dates);
		$min_date = strtotime($views[0][0]["date"]);
		$max_date = strtotime($views[sizeof($views) - 1][0]["date"]);
		//$cur_date = $min_date;
		for($cur_date = $min_date;$cur_date<=$max_date;$cur_date = $cur_date + 86400){
			$x=$cur_date;
			if(isset($dates[date("Y-m-d",$cur_date)])){
				
				$values[] = new scatter_value($x,$dates[date("Y-m-d",$cur_date)]);
			}else{
				
				$values[] = new scatter_value($x,0);
			}
			
		}
		if($max_date == $min_date){
			$max_date += 86400;
		}
		//pr($values);
		//pr($views);
		
		$line_dot = new line();
		$line_dot->set_values($values);
		$line_dot->set_text("Views");
		
		
		$chart->add_element($line_dot);
		
		
		
		$y = new y_axis();
		$y->set_range($min_value,$max_value,($max_value-$min_value)/10);
		$x = new x_axis();
		// grid line and tick every 10
		$x->set_range(
		mktime(0, 0, 0, date("m",$min_date), date("d",$min_date),date("Y",$min_date)),
		mktime(0, 0, 0, date("m",$max_date), date("d",$max_date),date("Y",$max_date))
		);
		// show ticks and grid lines for every day:
		$x->set_steps(86400);
		
		$labels = new x_axis_labels();
		
		// tell the labels to render the number as a date:
		$labels->text('#date:d-m-Y#');
		// generate labels for every day
		$labels->set_steps(86400);
		// only display every other label (every other day)
		$labels->visible_steps(ceil(($max_date-$min_date)/86400/20));
		$labels->rotate(90);
		$x->set_labels($labels);
		$chart->set_x_axis($x);
		$chart->set_y_axis($y);
		$chart->set_bg_colour("#FFFFFF");
		//print_r($views);
		//$this->set("allviews",$views[0][0]['count(*)']);
		
		
		$line_dot = new line();
		$line_dot->set_values(array(2,1));
		$line_dot->set_text("Unique views");
		$line_dot->colour("#0000000");
		//$chart->add_element($line_dot);
		
		//
		
		
		

		echo $chart->toPrettyString();
		exit();
		
		//$this->render(false);
	}
	function admin_stats($id = null) {
		set_time_limit(120);
		//Configure::write('debug', 2);
		
		$newsletter = null;
		if($id){
			$newsletter = $this->Newsletter->read(null, $id);
		}
		
		if (empty($newsletter)) {
			$this->Session->setFlash(__d('newsletter','Invalid Newsletter.', true));
			debug('Invalid Newsletter.');
			$this->redirect(array('action'=>'index'));
		}
		
		//// init sender class ////
		App::import('Lib', 'Newsletter.ClassCollection');
		$senderOpt = NewsletterConfig::load('sender');
		if(!is_array($senderOpt)){
			$senderOpt = array('name' => $senderOpt);
		}
		$sender = ClassCollection::getObject('NewsletterSender',$senderOpt['name']);
		
		//// queries ////
		$queries = array(
			'sended_count' => array(
				'conditions'=>array(
					'NewsletterSended.newsletter_id'=>$id
				),
				'type' => 'count'
			),
			"allviews" => array(
				'conditions'=>array(
					'NewsletterSended.newsletter_id'=>$id,
					'or' => array(
						'NewsletterStat.action' => 'view',
						array(
							'NewsletterStat.action IS NULL',
							'NewsletterStat.url' => null,
						)
					)
				),
				'model'=>'NewsletterStat',
				'type' => 'count'
			),
			"uniqueviews" => array(
				'fields'=>array(
					'count(DISTINCT NewsletterStat.sended_id) as uniqueviews'
				),
				'conditions'=>array(
					'NewsletterSended.newsletter_id'=>$id,
					'or' => array(
						'NewsletterStat.action' => 'view',
						array(
							'NewsletterStat.action IS NULL',
							'NewsletterStat.url' => null,
						)
					)
				),
				'model'=>'NewsletterStat',
				'type' => 'first'
			),
			"clickedlinks"  => array(
				'conditions'=>array(
					'NewsletterSended.newsletter_id'=>$id,
					'or' => array(
						'NewsletterStat.action' => 'click',
						array(
							'NewsletterStat.action IS NULL',
							'NewsletterStat.url IS NOT NULL',
						)
					)
				),
				'model'=>'NewsletterStat',
				'type' => 'count'
			),
			"uniqueclics"  => array(
				'fields'=>array(
					'count(DISTINCT NewsletterStat.sended_id) as uniqueclics'
				),
				'conditions'=>array(
					'NewsletterSended.newsletter_id'=>$id,
					'or' => array(
						'NewsletterStat.action' => 'click',
						array(
							'NewsletterStat.action IS NULL',
							'NewsletterStat.url IS NOT NULL',
						)
					)
				),
				'model'=>'NewsletterStat',
				'type' => 'first'
			),
			'toppages' => array(
				'fields'=>array(
					'count(*)',
					'NewsletterStat.url'
				),
				'conditions'=>array(
					'NewsletterSended.newsletter_id'=>$id,
					'or' => array(
						'NewsletterStat.action' => 'click',
						array(
							'NewsletterStat.action IS NULL',
							'NewsletterStat.url IS NOT NULL',
						)
					)
				),
				'model'=>'NewsletterStat',
				'group' => 'NewsletterStat.url',
				'order' => 'count(*) DESC',
				'limit' => 10,
				'type' => 'all'
			)
		);
		
		//// beforeStats callback ////
		if(method_exists($sender,'beforeStats')){
			$res = $sender->beforeStats($this,$newsletter,$queries);
			if(!empty($res) && is_array($res)){
				$queries = $res;
			}
		}
		
		//print_r($newsletter);
		//$newsletter = array();
		
		//// Execute Queries ////
		
		foreach($queries as $key => $q){
			$res = $this->_stat_query($q);
			if($q['type'] == 'count'){
					$stats[$key] = $res;
			}elseif($q['type'] == 'first'){
					if(!empty($q['fields'])){
						foreach($q['fields'] as $fkey => $f){
							if(is_numeric($fkey)){
								$fkey = $key;
							}
							$val = null;
							if(preg_match('/^([0-9a-z_]+).([0-9a-z_]+)$/i',$f,$match)){
								$val = Set::extract($f,$res);
							}elseif(preg_match('/ as ([0-9a-z_]+)$/i',$f,$match)){
								$val = $res[0][$match[1]];
							}elseif(!empty($res[$model->alias][$f])){
								$val = $res[$model->alias][$f];
							}elseif(!empty($res[0][$f])){
								$val = $res[0][$f];
							}
							$stats[$fkey] = $val;
						}
					}else{
						$stats[$key] = $res;
					}
				}else{
					$stats[$key] = $res;
				}
			}
		
		//debug($stats);
		
		
		//$unique_views = $this->NewsletterStats->find('count',array('conditions'=>array('newsletter_id'=>$id)));
		//$this->set("sended_count",$sended_count);
		
		
		if(method_exists($sender,'afterStats')){
			$res = $sender->afterStats($this,$newsletter,$stats);
			if(!empty($res)){
				$stats = $res;
			}
		}
		
		$this->set('Newsletter', $newsletter);
		$this->set($stats);
		
		//$newsletterSended = $this->NewsletterSended->find('all', array('conditions'=>array('newsletter_id'=>$id)));
		$newsletterSended = array();
		$this->set('newsletterSended', $newsletterSended);
	}
	
	function _stat_query(&$q){
		if(is_string($q) || !empty($q['sql'])){
			if(is_string($q)){
				$q = array('sql' => $q);
			}
			$q['type'] = 'all';
			$res = $this->NewsletterSended->query($sql);
			if(!empty($q['extract'])){
				$res = Set::extract($q['extract'],$res);
			}
		}else{
			$final = $q;
			unset($final['type']);
			unset($final['model']);
			if(empty($q['type'])) $q['type'] = 'all';
			if(empty($q['model'])) $q['model'] = $this->NewsletterSended;
			if(is_string($q['model'])){
				if(!empty($this->{$q['model']})) {
					$q['model'] = $this->{$q['model']};
				}else{
					$q['model'] = ClassRegistry::init($q['model']);
				}
			}
			$res = $q['model']->find($q['type'],$q);
		}
		
		return $res;
	}
	
	function admin_excel($id = null){
		//echo getcwd();
		//$excel = new PHPExcel();
		$objReader = PHPExcel_IOFactory::createReader('Excel2007');
		//$objPHPExcel = $objReader->load();
		$objPHPExcel = $objReader->load(APP.'plugins'.DS.'newsletter'.DS.'vendors'.DS.'template.xlsx');
		//var_dump($excel);
		$sql = "select email,count(*)'cnt',GROUP_CONCAT(url)'url' from newsletter_sended NewsletterSended  left join newsletter_stats NewsletterStats on  NewsletterSended.id = NewsletterStats.sended_id where NewsletterSended.newsletter_id = '".$id."' group by email order by email,url";
		$email_read = $this->NewsletterSended->query($sql);
		//print_r($email_read);
		//$this->set("email_read",$views[0][0]['count(*)']);
		
		$sql = "select * from newsletter_sended NewsletterSended  where NewsletterSended.newsletter_id = '".$id."' and (select count(*) from newsletter_stats NewsletterStats where NewsletterStats.sended_id = NewsletterSended.id ) = 0 order by email";
		$email_notread = $this->NewsletterSended->query($sql);
		//$this->set("email_notread",$views[0][0]['count(*)']);
		
		$row_sheet_index=0;
		$row_index =0;
		$cc =0;
		foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
			if($cc == 0){
				$worksheet->setTitle("Courriels ouvert");
				$row_sheet_index=0;
				foreach($email_read as $email){
					$worksheet->setCellValueByColumnAndRow(0,$row_sheet_index + 2, $email["NewsletterSended"]["email"]);
					$worksheet->setCellValueByColumnAndRow(1,$row_sheet_index + 2, $email[0]["cnt"]);
					$split = explode(",",$email[0]["url"]);
					$split_index = 0;
					foreach($split as $ss){
						$worksheet->setCellValueByColumnAndRow(2 + $split_index,$row_sheet_index + 2, $ss);
						$split_index++;
					}	
					
					$row_sheet_index++;
					
				}
			}else{
				$row_sheet_index=0;
				$worksheet->setTitle("Courriels non-ouvert");
				foreach($email_notread as $email){
					$worksheet->setCellValueByColumnAndRow(0,$row_sheet_index + 2, $email["NewsletterSended"]["email"]);
					$row_sheet_index++;
				}
			}
			$cc++;
			//break;
			//$worksheet->setCellValueByColumnAndRow($key,$row_sheet_index + 5, $arr[$row_index][$val]);
			
		}
		
		
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="export.xlsx"');
		header('Cache-Control: max-age=0');
		
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		exit;
	
	}
	function admin_preview($id = null) {
		if(Configure::read('debug')==2){
			Configure::write('debug', 1);
		}
		$this->autoLayout = true;
		$this->layout = "newsletter";
		if (!$id) {
			$this->Session->setFlash(__d('newsletter','Invalid Newsletter.', true));
			//debug('Invalid Newsletter.');
			$this->redirect(array('action'=>'index'));
		}
		//debug($this->params);
		$newsletter = $this->Newsletter->read(null, $id);
		$newsletter_boxes = $this->NewsletterBox->find('all',array('conditions'=>array('NewsletterBox.newsletter_id'=>$id),'order'=>'NewsletterBox.zone ASC, NewsletterBox.order ASC'));
		$boxes_by_zone = array();
		foreach($newsletter_boxes as $box){
			$boxes_by_zone[$box['NewsletterBox']['zone']][] = $box;
		}
		$config = $this->Newsletter->getConfig($this->Newsletter->data);
		if(!empty($config)){
			$config->beforeRender($this->Newsletter->data,$this);
		}
		$this->set('newsletter', $this->Newsletter->data);
		$this->set('boxes_by_zone',$boxes_by_zone);
		$this->set('newsletter_data', $this->Newsletter->data);
		$this->set('title_for_newsletter', '<span id="title_for_newsletter">'.$newsletter['Newsletter']['title'].'</span>');
		//$this->set('text_for_newsletter', '<div id="text_for_newsletter">'.$newsletter['Newsletter']['text'].'</div>');
		
		return $this->render('/elements/newsletter/'.$newsletter['Newsletter']['template']);
	}
	function admin_make($id = null) {
		if(Configure::read('debug')==2){
			Configure::write('debug', 1);
		}
		$this->autoLayout = true;
		$this->layout = "newsletter";
		if (!$id) {
			debug('Invalid Newsletter.');
			return false;
		}else{
			$newsletter = $this->Newsletter->read(null, $id);
			$newsletter_boxes = $this->NewsletterBox->find('all',array('conditions'=>array('NewsletterBox.newsletter_id'=>$id),'order'=>'NewsletterBox.zone ASC, NewsletterBox.order ASC'));
			$boxes_by_zone = array();
			foreach($newsletter_boxes as $box){
				$boxes_by_zone[$box['NewsletterBox']['zone']][] = $box;
			}
			$config = $this->Newsletter->getConfig($this->Newsletter->data);
			if(!empty($config)){
				$config->beforeRender($this->Newsletter->data,$this);
			}
			$this->set('newsletter',$this->Newsletter->data);
			$this->set('boxes_by_zone',$boxes_by_zone);
			$this->set('newsletter_data', $this->Newsletter->data);
			$this->set('title_for_newsletter', '<span id="title_for_newsletter">'.$newsletter['Newsletter']['title'].'</span>');
			
			
			return $this->render('/elements/newsletter/'.$newsletter['Newsletter']['template']);
		}
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
		
		$this->set('templates',$this->Funct->getTemplates());
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
			if($newsletter['Newsletter']['template'] != $this->data['Newsletter']['template']){
				$this->Newsletter->save($this->data,true,array('id','template'));
			}
			$this->data['Newsletter']['html'] = $this->requestAction('admin/newsletter/newsletter/make/'.$id);
			$this->data['Newsletter']['tested'] = 0;
			if(empty($this->data['Newsletter']['associated'])){
				$this->data['Newsletter']['associated'] = array();
			}
			if ($this->Newsletter->save($this->data)) {
				$this->Session->setFlash(__d('newsletter','The Newsletter has been saved', true));
				$this->Session->delete('EditedNewsletter');
				$this->redirect(array('action'=>'index'));
			} else {
				$this->Session->setFlash(__d('newsletter','The Newsletter could not be saved. Please, try again.', true));
			}
		}
		if (empty($this->data)) {
			$this->data = $newsletter;
		}
		$this->NewsletterBox->recursive = -1;
		$newsletter_boxes = $this->NewsletterBox->find('all',array('conditions'=>array('NewsletterBox.newsletter_id'=>$id),'order'=>'NewsletterBox.zone ASC, NewsletterBox.order ASC'));
		$boxes_by_zone = array();
		foreach($newsletter_boxes as $box){
			$boxes_by_zone[$box['NewsletterBox']['zone']][] = $box;
		}
		
		$langs = NewsletterConfig::load('langs');
		if(!empty($langs)){
			$newsletterByLang = $this->Newsletter->find('list',array('fields'=>array('id','title','lang'),'conditions'=>array('id NOT'=>$id,'Newsletter.lang IS NOT NULL'), 'recursive' => -1));
			$this->set('newsletterByLang',$newsletterByLang);
		}
		$config = $this->Newsletter->getConfig($this->data);
		if(!empty($config)){
			$config->beforeRenderEdit($this->data,$this);
		}
		$this->set('newsletter',$this->data);
		$this->set('boxes_by_zone',$boxes_by_zone);
		$this->set('templates',$this->Funct->getTemplates());
		$this->set('box_elements',$this->Funct->getBoxElements($this->data['Newsletter']['template']));
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
		$this->render(array('/elements/newsletter_box/'.$newsletter['Newsletter']['template'].'/'.$newsletter_box["NewsletterBox"]["template"].'_edit','/elements/newsletter_box/'.$newsletter_box["NewsletterBox"]["template"].'_edit'));
		//$this->render('/elements/newsletter_box/'.$newsletter_box["NewsletterBox"]["template"]."_edit");
	}
	function admin_add_box($newsletter_id,$zone,$boxElement = null) {
		$this->autoRender = false;
		if(Configure::read('debug')==2){
			Configure::write('debug', 1);
		}
		//debug($this->params);
		$this->layout = "newsletter_box_ajax";
		
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
				$this->render(array('/elements/newsletter_box/'.$newsletter['Newsletter']['template'].'/'.$boxElement,'/elements/newsletter_box/'.$boxElement));
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
		$this->layout = "newsletter_box_ajax";
		
		$newsletter_box = $this->NewsletterBox->read(null, $id);
		if (!empty($this->data)) {
			//debug($this->data);
			if(Configure::read('App.encoding') && strtolower(Configure::read('App.encoding')) != "utf-8" && $this->RequestHandler->isAjax()){
				$this->data = $this->Funct->array_map_recursive("utf8_decode",$this->data);
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
						$uploaded_files[$name] = $this->Funct->upload($file);
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
			//$this->data = $this->Funct->encode_box($this->data);
			//debug($this->data);
			if ($this->NewsletterBox->save($this->data)) {

			}
		}
		$newsletter_box = $this->NewsletterBox->read(null, $id);
		$newsletter = $this->Newsletter->read(null, $newsletter_box["NewsletterBox"]["newsletter_id"]);
		$this->set('newsletter_box',$newsletter_box);
		$this->set('newsletter',$newsletter);
		//$this->render('/elements/newsletter_box/'.$newsletter_box["NewsletterBox"]["template"]);
		$this->render(array('/elements/newsletter_box/'.$newsletter['Newsletter']['template'].'/'.$newsletter_box["NewsletterBox"]["template"],'/elements/newsletter_box/'.$newsletter_box["NewsletterBox"]["template"]));
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
	
	function admin_import_template(){
		$available = true;
		$available = $available && class_exists('ZipArchive');
		
		if(!file_exists(APP.'views'.DS.'elements'.DS.'newsletter')){
			$available = $available && mkdir(APP.'views'.DS.'elements'.DS.'newsletter',  0777);
		}
		if($available){
			$available = $available && (fileperms(APP.'views'.DS.'elements'.DS.'newsletter') & 0x0002 != 0);
		}
		
		if(!file_exists(APP.'config'.DS.'plugins')){
			$available = $available && mkdir(APP.'config'.DS.'plugins',  0777);
		}
		if(!file_exists(APP.'config'.DS.'plugins'.DS.'newsletter')){
			$available = $available && mkdir(APP.'config'.DS.'plugins'.DS.'newsletter',  0777);
		}
		if($available){
			$available = $available && (fileperms(APP.'config'.DS.'plugins'.DS.'newsletter') & 0x0002 != 0);
		}
		
		if(!file_exists(TMP.'newsletter')){
			$available = $available && mkdir(TMP.'newsletter',  0777);
		}
		if($available){
			$available = $available && (fileperms(TMP.'newsletter') & 0x0002 != 0);
		}
		
		if(!file_exists(WWW_ROOT.'img'.DS.'newsletter')){
			$available = $available && mkdir(WWW_ROOT.'img'.DS.'newsletter',  0777);
		}
		if($available){
			$available = $available && (fileperms(WWW_ROOT.'img'.DS.'newsletter') & 0x0002 != 0);
		}
		$this->set('available',$available);
		
		if($available){
			if (!empty($this->data)) {
				//debug($this->data);
				if(!empty($this->data['Newsletter']['zip_file']) && $this->data['Newsletter']['zip_file']['error'] == 0  && $this->data['Newsletter']['zip_file']['type'] == 'application/zip'){
					$i = 0;
					while(file_exists(TMP.'newsletter'.DS.'import_'.$i.'.zip')){
						$i++;
					}
					if(move_uploaded_file($this->data['Newsletter']['zip_file']['tmp_name'],TMP.'newsletter'.DS.'import_'.$i.'.zip')){
						if($this->_import_zip(TMP.'newsletter'.DS.'import_'.$i.'.zip',$this->data['Newsletter']['title'])){
							$this->Session->setFlash(__d('newsletter','Newsletter template imported', true));
							$this->redirect(array('action'=>'index'));
						}else{
							$this->Session->setFlash(__d('newsletter','Error reading zip file', true));
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
	
	function _import_zip($file,$name){
		
		$zip = new ZipArchive;
		$res = $zip->open($file);
		if (!$res) {
			return false;
		}
		$newsletterFileName = strtolower(Inflector::slug($name));
		
		///////// create needed folder /////////
		if(!file_exists(WWW_ROOT.'img'.DS.'newsletter'.DS.$newsletterFileName)){
			if(!mkdir(WWW_ROOT.'img'.DS.'newsletter'.DS.$newsletterFileName,  0777)){
				return false;
			}
		}
		
		///////// map files /////////
		$contentFileFilter = array('/^html.html$/','/^(?:[^\/]*\/)?html.html$/','/^(?:[^\/]*\/)?index.html$/');
		$imageFilter = '/^(?:[^\/]*\/)?(img|images)\/.*\.(jpg|gif|png)$/';
		$images = array();
		for ($i = 0; $i < $zip->numFiles; $i++) {
			$filename = $zip->getNameIndex($i);
			if(preg_match($imageFilter,$filename)){
				$images[] = $filename;
			}
			foreach($contentFileFilter as $priority => $filter){
				if(preg_match($filter,$filename)){
					if(empty($contentFile) || $contentFilePriority>=$priority){
						$contentFile = $filename;
						$contentFilePriority = $priority;
					}
				}
			}
		}
		
		
		///////// extract and format content /////////
		if(empty($contentFile)){
			return false;
		}
		$content = $zip->getFromName($contentFile);
		if(empty($content)){
			return false;
		}
		$content = preg_replace('/="\/?(?:http:\/\/[^"\']*\/)?(img|images)\/([^"\']*)"/','="<?php echo \$html->url(\'/img/newsletter/'.$newsletterFileName.'/$1\',true); ?>"',$content);
		$content = preg_replace('/href="([^"\']*)"/','href="<?php echo $this->NewsletterMaker->url(\'$1\'); ?>"',$content);
		file_put_contents (APP.'views'.DS.'elements'.DS.'newsletter'.DS.$newsletterFileName.'.ctp' , $content);
		
		
		///////// create config file /////////
		$pluginPath = App::pluginPath('Newsletter');
		ob_start();
		include($pluginPath.'vendors'.DS.'config_template.php');
		$configFile = ob_get_clean();
		file_put_contents (APP.'config'.DS.'plugins'.DS.'newsletter'.DS.$newsletterFileName.'.php' , $configFile);
		
		
		///////// extract images /////////
		$this->_zipExtractToFlat($zip,WWW_ROOT.'img'.DS.'newsletter'.DS.$newsletterFileName,$images);
		
		$zip->close();
		return true;
	}
	
	function _zipExtractToFlat($zip, $dest, $entries = null){
		if(is_null($entries)){
			$entries = array();
			for ($i = 0; $i < $zip->numFiles; $i++) {
				$entries[] = $zip->getNameIndex($i);
			}
		}
		foreach($entries as $entry)
        {
            if ( substr( $entry, -1 ) == '/' ) continue; // skip directories
           
            $fp = $zip->getStream( $entry );
            $ofp = fopen( $dest.DS.basename($entry), 'w' );
           
            if ( ! $fp )
                throw new Exception('Unable to extract the file.');
           
            while ( ! feof( $fp ) )
                fwrite( $ofp, fread($fp, 8192) );
           
            fclose($fp);
            fclose($ofp);
        } 
	}
}
?>