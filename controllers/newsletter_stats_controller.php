<?php
class NewsletterStatsController extends NewsletterAppController {

	var $name = 'NewsletterStats';
	var $uses = array('Newsletter.Newsletter','Newsletter.NewsletterSended','Newsletter.NewsletterEvent','Newsletter.NewsletterStat');
	var $components = array('RequestHandler');
	
	function admin_index($id = null) {
		if($this->RequestHandler->isAjax() || !empty($this->params['named']['ajax'])){
			Configure::write('debug',0);
			$this->layout = 'ajax';
			$this->ajax = true;
			$this->set('ajax',true);
		}
		set_time_limit(120);
		//Configure::write('debug', 2);
		
		$newsletter = null;
		if($id){
			$this->Newsletter->recursive = -1;
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
				'mode' => 'count'
			),
			"allviews" => array(
				'conditions'=>array(
					'NewsletterSended.newsletter_id'=>$id,
					'or' => array(
						'NewsletterEvent.action' => 'view',
						array(
							'NewsletterEvent.action IS NULL',
							'NewsletterEvent.url' => null,
						)
					)
				),
				'model'=>'NewsletterEvent',
				'mode' => 'count'
			),
			"uniqueviews" => array(
				'fields'=>array(
					'count(DISTINCT NewsletterEvent.sended_id) as uniqueviews'
				),
				'conditions'=>array(
					'NewsletterSended.newsletter_id'=>$id,
					'or' => array(
						'NewsletterEvent.action' => 'view',
						array(
							'NewsletterEvent.action IS NULL',
							'NewsletterEvent.url' => null,
						)
					)
				),
				'model'=>'NewsletterEvent',
				'mode' => 'first'
			),
			"clickedlinks"  => array(
				'conditions'=>array(
					'NewsletterSended.newsletter_id'=>$id,
					'or' => array(
						'NewsletterEvent.action' => 'click',
						array(
							'NewsletterEvent.action IS NULL',
							'NewsletterEvent.url IS NOT NULL',
						)
					)
				),
				'model'=>'NewsletterEvent',
				'mode' => 'count'
			),
			"uniqueclics"  => array(
				'fields'=>array(
					'count(DISTINCT NewsletterEvent.sended_id) as uniqueclics'
				),
				'conditions'=>array(
					'NewsletterSended.newsletter_id'=>$id,
					'or' => array(
						'NewsletterEvent.action' => 'click',
						array(
							'NewsletterEvent.action IS NULL',
							'NewsletterEvent.url IS NOT NULL',
						)
					)
				),
				'model'=>'NewsletterEvent',
				'mode' => 'first'
			),
			'toppages' => array(
				'fields'=>array(
					'count(*)',
					'NewsletterEvent.url'
				),
				'conditions'=>array(
					'NewsletterSended.newsletter_id'=>$id,
					'or' => array(
						'NewsletterEvent.action' => 'click',
						array(
							'NewsletterEvent.action IS NULL',
							'NewsletterEvent.url IS NOT NULL',
						)
					)
				),
				'model'=>'NewsletterEvent',
				'group' => 'NewsletterEvent.url',
				'order' => 'count(*) DESC',
				'limit' => 10,
				'mode' => 'all'
			)
		);
		
		$stats = $this->_get_stats($queries,$newsletter,$sender);
		
		//debug($stats);
		
		
		$this->set('Newsletter', $newsletter);
		$this->set($stats);
		
		//$newsletterSended = $this->NewsletterSended->find('all', array('conditions'=>array('newsletter_id'=>$id)));
		$newsletterSended = array();
		$this->set('newsletterSended', $newsletterSended);
	}
	
	
	function admin_excel($id = null){
		//echo getcwd();
		//$excel = new PHPExcel();
		$objReader = PHPExcel_IOFactory::createReader('Excel2007');
		//$objPHPExcel = $objReader->load();
		$objPHPExcel = $objReader->load(APP.'plugins'.DS.'newsletter'.DS.'vendors'.DS.'template.xlsx');
		//var_dump($excel);
		$sql = "select email,count(*)'cnt',GROUP_CONCAT(url)'url' from newsletter_sended NewsletterSended  left join newsletter_stats NewsletterEvent on  NewsletterSended.id = NewsletterEvent.sended_id where NewsletterSended.newsletter_id = '".$id."' group by email order by email,url";
		$email_read = $this->NewsletterSended->query($sql);
		//print_r($email_read);
		//$this->set("email_read",$views[0][0]['count(*)']);
		
		$sql = "select * from newsletter_sended NewsletterSended  where NewsletterSended.newsletter_id = '".$id."' and (select count(*) from newsletter_stats NewsletterEvent where NewsletterEvent.sended_id = NewsletterSended.id ) = 0 order by email";
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
	
	function admin_graphs($id = null) {
		App::import('Vendor', 'Newsletter.php-ofc-library', array('file'=>'php-ofc-library/open-flash-chart.php'));
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
		$opt = array(
			'type'=>'graph',
			'query'=>array(
				'fields' => array('count(*) as nb','DATE(NewsletterEvent.date) as date'),
				'conditions'=>array(
					'NewsletterSended.newsletter_id'=>$id,
					'or' => array(
						'NewsletterEvent.action' => 'view',
						array(
							'NewsletterEvent.action IS NULL',
							'NewsletterEvent.url' => null,
						)
					)
				),
				'group' => 'DATE(NewsletterEvent.date)',
				'order' => 'DATE(NewsletterEvent.date)',
				'model'=>'NewsletterEvent',
			)
		);
		
		$data = $this->_get_stats(array('viewByDays'=>$opt),$newsletter,$sender);
		//debug($data);
		$dates = $data['viewByDays'];
		
		$min_value = 99999999;
		$max_value = 0;
		foreach($dates as $d => $val){
			if($val < $min_value){
				$min_value = $val;
			}
			if($val > $max_value){
				$max_value = $val;
			}
		}
		if($max_value == $min_value){
			$max_value++;
			$min_value--;
		}
		$min_value = 0;
		//debug($dates);
		$min_date = key($dates);
		end($dates);
		$max_date = key($dates);
		
		for($cur_date = $min_date;$cur_date<=$max_date;$cur_date = $cur_date + 86400){
			$x=strtotime('00:00:00',$cur_date);
			if(isset($dates[$cur_date])){
				$values[] = new scatter_value($x,$dates[$cur_date]);
			}else{
				$values[] = new scatter_value($x,0);
			}
		}
		if($max_date == $min_date){
			$max_date += 86400;
		}
		//debug($values);
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
	
	function _get_stats($opts,$newsletter,$sender){
		
		$defOpt = array(
			'name' => null,
			'cache' => '+1 Day',
			'query' => null,
			'type' => 'single',
			'exec' => null,
			'async' => false,
			'modeTypeMap' => array('all'=>'serialized','count'=>'single','first'=>'multiple')
		);
		
		//// beforeStats callback ////
		if(method_exists($sender,'beforeStats')){
			$res = $sender->beforeStats($this,$newsletter,$opts);
			if(!empty($res) && is_array($res)){
				$opts = $res;
			}
		}
		
		//print_r($newsletter);
		//$newsletter = array();
		
		//// Execute ////
		$statsCache = $this->NewsletterStat->getStats($newsletter['Newsletter']['id'],array_keys($opts));
		//debug($statsCache);
		$stats = array();
		foreach($opts as $key => $opt){
			$sData = array();
			//// parse Opt ////
			if(!count(array_intersect_key($opt,$defOpt))){
				$opt = array('query'=>$opt);
			}
			if(empty($opt['type']) && !empty($opt['query']['mode'])) {
				$opt['type'] = $defOpt['modeTypeMap'][$opt['query']['mode']];
			}
			$opt = array_merge($defOpt,$opt);
			if(empty($opt['key'])) $opt['key'] = $key;
			
			//// Check Cache ////
			$cached = false;
			$cache = array();
			if(!empty($statsCache[$opt['key']])){
				$cache = $statsCache[$opt['key']];
				$cached = !empty($opt['cache']) && (strtotime($opt['cache'],strtotime($cache[0]['modified'])) > mktime());
				if($opt['type'] == 'single'){
					$sData[$opt['key']] = $cache[0]['val'];
				}elseif($opt['type'] == 'multiple'){
					$ordered = array();
					foreach($cache as $cdata){
						$sData[$cdata['context']] = $cdata['val'];
						$ordered[$cdata['context']] = $cdata;
					}
					$cache = $ordered;
					unset($ordered);
				}elseif($opt['type'] == 'graph'){
					$ordered = array();
					foreach($cache as $cdata){
						$sData[$opt['key']][strtotime($cdata['date'])] = $cdata['val'];
						$ordered[strtotime($cdata['date'])] = $cdata;
					}
					$sData[$opt['key']] = array_reverse($sData[$opt['key']],true);
					$cache = $ordered;
					unset($ordered);
				}
			}
			
			if(!$cached){
				////  ////
				if(!empty($opt['async']) && empty($this->params['named']['async'])){
					$this->viewVars['toUpdate'][] = $opt['key'];
					continue;
				}
				if($opt['exec']){
					if(method_exists($sender,$opt['exec'])){
						$res = $sender->{$opt['exec']}($opt,$newsletter);
						if(isset($res)){
							if(is_array($res) && $opt['type'] == 'multiple'){
								$sData = $res;
							}else{
								$sData[$opt['key']] = $res;
							}
						}
					}
				}
				if($opt['query']){
					$sData = $this->_stat_query($opt);
				}
				
				//// Set Cache ////
				if(!empty($sData) && !empty($opt['cache'])){
					if($opt['type'] == 'single'){
						$this->NewsletterStat->create();
						$cdata = array(
							'newsletter_id'=>$newsletter['Newsletter']['id'],
							'val' => reset($sData),
							'name' => $opt['key'],
						);
						if(!empty($cache[0])){
							$cdata['id'] = $cache[0]['id'];
						}
						$this->NewsletterStat->save($cdata);
					}elseif($opt['type'] == 'multiple'){
						foreach($sData as $context => $val){
							$this->NewsletterStat->create();
							$cdata = array(
								'newsletter_id'=>$newsletter['Newsletter']['id'],
								'val' => $val,
								'name' => $opt['key'],
								'context' => $context,
							);
							if(!empty($cache[$context])){
								$cdata['id'] = $cache[$context]['id'];
							}
							$this->NewsletterStat->save($cdata);
						}
					}elseif($opt['type'] == 'graph'){
						foreach($sData[$opt['key']] as $date => $val){
							$format = $this->NewsletterStat->getDataSource()->columns['datetime']['format'];
							$this->NewsletterStat->create();
							$cdata = array(
								'newsletter_id'=>$newsletter['Newsletter']['id'],
								'val' => $val,
								'name' => $opt['key'],
								'date' => date($format,$date),
							);
							if(!empty($cache[$date])){
								$cdata['id'] = $cache[$date]['id'];
							}
							$this->NewsletterStat->save($cdata);
						}
					}
				}
			}
			
			if(!empty($sData)){
				$stats = array_merge($stats,$sData);
			}
		}
		
		//// afterStats callback ////
		if(method_exists($sender,'afterStats')){
			$res = $sender->afterStats($this,$newsletter,$stats);
			if(!empty($res)){
				$stats = $res;
			}
		}
		
		return $stats;
	}
	
	function _stat_query($opt){
		$q = $opt['query'];
		$key = $opt['key'];
		if(is_string($q) || !empty($q['sql'])){
			if(is_string($q)){
				$q = array('sql' => $q);
			}
			$q['mode'] = 'all';
			$res = $this->NewsletterSended->query($sql);
			if(!empty($q['extract'])){
				$res = Set::extract($q['extract'],$res);
			}
		}else{
			$final = $q;
			unset($final['mode']);
			unset($final['model']);
			if(empty($q['mode'])) $q['mode'] = 'all';
			if(empty($q['model'])) $q['model'] = $this->NewsletterSended;
			if(is_string($q['model'])){
				if(!empty($this->{$q['model']})) {
					$q['model'] = $this->{$q['model']};
				}else{
					$q['model'] = ClassRegistry::init($q['model']);
				}
			}
			$res = $q['model']->find($q['mode'],$q);
		}
		
		if($opt['type'] == 'single'){
			$stats[$key] = $res;
		}elseif($opt['type'] == 'multiple'){
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
		}elseif($opt['type'] == 'graph'){
			$dates = array();
			$keyPath = '0.date';
			$valPath = '0.nb';
			foreach($res as $r){
				$dates[strtotime(Set::extract($keyPath,$r))] = Set::extract($valPath,$r);
			}
			$stats[$key] = $dates;
		}else{
			$stats[$key] = $res;
		}
		
		return $stats;
	}
}
?>