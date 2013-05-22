<?php
class NewsletterSendlistsController extends NewsletterAppController {

	var $name = 'NewsletterSendlists';
	var $helpers = array('Html', 'Form');
	var $components = array('Newsletter.Funct');

	/*function index() {
		$this->NewsletterSendlist->recursive = 0;
		$this->set('newsletterSendlists', $this->paginate());
	}

	function view($id = null) {
		if (!$id) {
			$this->Session->setFlash(__('Invalid NewsletterSendlist.', true));
			$this->redirect(array('action'=>'index'));
		}
		$this->set('newsletterSendlist', $this->NewsletterSendlist->read(null, $id));
	}*/

	function admin_index() {

		$this->NewsletterSendlist->recursive = -1;
		$this->paginate['order'] =  array('NewsletterSendlist.order' => 'asc');
		$lists = $this->paginate();
		$this->NewsletterSendlist->NewsletterEmail->recursive = -1;
		
		$tableSendlists = $this->Funct->getTableSendlists();
		$restrictedSendlists = array_keys($tableSendlists);
		
		foreach($lists as &$list){
			if(in_array($list[$this->NewsletterSendlist->alias]['id'],$restrictedSendlists)){
				$findOptions = $this->Funct->tabledEmailGetFindOptions($list[$this->NewsletterSendlist->alias]['id']);
				unset($findOptions['fields']);
				$list[$this->NewsletterSendlist->alias]['nb_email'] = $findOptions['model']->find('count',$this->Funct->standardizeFindOptions($findOptions));
			}else{
				$list[$this->NewsletterSendlist->alias]['nb_email'] = $this->NewsletterSendlist->NewsletterEmail->find('count',array('conditions'=>array('sendlist_id'=>$list[$this->NewsletterSendlist->alias]['id'])));
			}
		}
		$this->set('newsletterSendlists', $lists);
		$this->set('restrictedSendlists',$restrictedSendlists);
	}

	function admin_import($id = null) {
		//$allowedTypes = array('application/vnd.ms-excel','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		$allowedExt = array('xls','xlsx');
		
		$moveFolder = TMP."newletter_import".DS;
		App::import('Lib', 'Newsletter.SetMulti');
		$filename = SetMulti::extractHierarchic(array('params.named.filename','params.filename','data.NewsletterSendlist.filename'),$this);
		if(!empty($filename)){
			$moveFullPath = $moveFolder.$filename;
			$this->data['NewsletterSendlist']['filename'] = $filename;
		}
		if(!empty($this->data)){
			if(!empty($this->data['NewsletterSendlist']['import_file'])){
				$uploadFile = $this->data['NewsletterSendlist']['import_file'];
				$ext = pathinfo($uploadFile['name'], PATHINFO_EXTENSION);
				if(!in_array($ext,$allowedExt)){ //if(!in_array($uploadFile['type'],$allowedTypes)){
					debug($uploadFile);
					$this->Session->setFlash(__d('newsletter','This file type is not supported.', true));
				}elseif($uploadFile['error'] == UPLOAD_ERR_OK){
					$folderOk = is_dir($moveFolder);
					if(!$folderOk){
						if (mkdir($moveFolder, 0777)){
							$folderOk = true;
						}else{
							$this->Session->setFlash(__d('newsletter','could not Create upload folder', true));
						}
					}
					if($folderOk){
						$filename = pathinfo($uploadFile['name'], PATHINFO_FILENAME);
						$ext = pathinfo($uploadFile['name'], PATHINFO_EXTENSION);
						$num = 0;
						do{
							$num++;
							$movePath = $filename.str_pad($num,3,'0',STR_PAD_LEFT).'.'.$ext;
							$moveFullPath = $moveFolder.$movePath;
						}while(file_exists($moveFullPath));
						//debug($moveFullPath);
						if (move_uploaded_file($uploadFile['tmp_name'], $moveFullPath)) {
							$this->data['NewsletterSendlist']['filename'] = $movePath;
							
							$this->redirect(array('filename'=>$movePath));
						} else {
							$this->Session->setFlash(__d('newsletter','Upload Error', true).'.');
						}
					}
				}else{
					$this->Session->setFlash(__d('newsletter','Upload Error', true));
				}
			}
		}
		$this->NewsletterSendlist->recursive = -1;
		$lists = $this->NewsletterSendlist->find('list');
		$this->set('lists', $lists);
		if(!empty($moveFullPath) && file_exists($moveFullPath)){
			if(!empty($this->data) && !empty($this->data['NewsletterSendlist']['cols'])){
				if(in_array('email',($this->data['NewsletterSendlist']['cols']))){
					$this->_import_xls($moveFullPath);
				}else{
					$this->Session->setFlash(__d('newsletter','You must at least select one column as the email field', true));
				}
			}
			$this->set($this->_parse_xls_cols($moveFullPath));
			$this->render('admin_import_cols');
		}else{
		}
	}
	
	function _parse_xls_cols($filePath){
		if(file_exists($filePath)){
			App::import('Vendor', 'Newsletter.php-ofc-library', array('file'=>'php-ofc-library/open-flash-chart.php'));
			App::import('Vendor', 'Newsletter.PHPExcel',array('file' => 'PHPExcel/IOFactory.php'));
			$objPHPExcel = PHPExcel_IOFactory::load($filePath);
			$objWorksheet = $objPHPExcel->getActiveSheet();
			$highestRow = $objWorksheet->getHighestRow();
			$highestColumn = $objWorksheet->getHighestColumn();
			$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn); 
			
			/////////////// cols config ///////////////
			$langs = Configure::read('languages');
			$fieldsAlias = array(
					'first_name' => array("first name", "firstname", "prenom"),
					'last_name'  => array("last name", "lastname", "nom", "nom de famille"),
					'email'      => array("email", "mail", "courriel")
			);
			$fieldsCharAlias = array(
				"é" => "e",
				"è" => "e",
				"ê" => "e",
				"_" => " ",
				"-" => " "
			);
			$fieldGess = array(
				'email' => '/^[\w-]+@([\w-]+\.)+[\w-]+$/',
				'phone' => '/^\(?[0-9]{3}\)?[-\s]?[0-9]{3}[-\s]?[0-9]{4}$/',
				'sex' => '/^(f|m)$/i',
				'name' => '/^[a-z\s-]+$/i',
			);
			$fields = $this->NewsletterSendlist->NewsletterEmail->schema();
			unset($fields['id'],$fields['created'],$fields['modified'],$fields['sendlist_id']);
			$fields = array_merge(array('first_name'=>array(),'last_name'=>array()),$fields);
			$defFieldsAlias = array();
			$fieldsList = array();
			foreach($fields as $f => $opt){
				$fieldsList[$f] = __(Inflector::humanize($f),true);
				$defFieldsAlias[$f] = array($f);
				$langTmp = Configure::read('Config.language');
				foreach($langs as $l){
					Configure::write('Config.language', 'Config.language', $l);
					if(!in_array(__($f,true),$defFieldsAlias[$f])){
						$defFieldsAlias[$f][] = __($f,true);
					}
					if(!in_array(__d('newsletter',$f,true),$defFieldsAlias[$f])){
						$defFieldsAlias[$f][] = __d('newsletter',$f,true);
					}
				}
				Configure::write('Config.language', $langTmp);
			}
			unset($defFieldsAlias['active']);
			$fieldsAlias = array_merge($defFieldsAlias,$fieldsAlias);
			//debug($fieldsAlias);
			$fieldGess = array_intersect_key($fieldGess,$fieldsAlias);
			//debug($fieldGess);
			
			
			/////////////// Test first row ///////////////
			$first_row = array();
			for ($col = 0; $col <= $highestColumnIndex; ++$col) {
				$cell = $objWorksheet->getCellByColumnAndRow($col, 1)->getValue();
				$cell = mb_strtolower($cell);
				$cell = str_replace(array_keys($fieldsCharAlias),array_values($fieldsCharAlias),$cell);
				$first_row[$col] = $cell;
			}
			//debug($first_row);
			$cols_fields = array_flip($first_row);
			App::import('Lib', 'Newsletter.SetMulti');
			$cols_fields = SetMulti::extractHierarchicMulti($fieldsAlias,$cols_fields);
			$cols_fields = SetMulti::filterNot($cols_fields,'is_null');
			$title_row = !empty($cols_fields);
			
			
			/////////////// Test data ///////////////
			$tmpGess = array_diff_key($fieldGess,$cols_fields);
			if(!empty($cols_fields['first_name'])){
				unset($tmpGess['name']);
			}
			for ($i = 0; $i < 50 && !empty($tmpGess) && $i < $highestRow; $i++) {
				$row = $this->_get_xls_row($objWorksheet,$i+1+$title_row);
				//debug($row);
				foreach($tmpGess as $f => $exp){
					foreach($row as $col => $val){
						if(!in_array($col,$cols_fields)){
							if(preg_match($exp,$val)){
								unset($tmpGess[$f]);
								$cols_fields[$f] = $col;
								break;
							}
						}
					}
				}
			}
			
			$cols_fields = array_flip($cols_fields);
			
			/////////////// teaser ///////////////
			$teaser = array();
			for ($i = 0; $i < 15 && $i < $highestRow; $i++) {
				$row = $this->_get_xls_row($objWorksheet,$i+1);
				$teaser[] = $row;
			}
			
			//debug($cols_fields);
			
			return array('cols'=>$cols_fields,'fields'=>$fieldsList,'showFirst'=>!$title_row,'teaser'=>$teaser);
		}
		return null;
	}
	
	
	function _get_xls_row($worksheet,$i){
		$highestColumn = $worksheet->getHighestColumn();
		$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn); 
		for ($col = 0; $col <= $highestColumnIndex; ++$col) {
			$cell = $worksheet->getCellByColumnAndRow($col, $i)->getValue();
			$row[$col] = trim($cell);
		}
		return $row;
	}
	function _import_xls($filePath){
		App::import('Vendor', 'Newsletter.php-ofc-library', array('file'=>'php-ofc-library/open-flash-chart.php'));
		App::import('Vendor', 'Newsletter.PHPExcel',array('file' => 'PHPExcel/IOFactory.php'));
		$objPHPExcel = PHPExcel_IOFactory::load($filePath);
		$objWorksheet = $objPHPExcel->getActiveSheet();
		$highestRow = $objWorksheet->getHighestRow();
		$highestColumn = $objWorksheet->getHighestColumn();
		$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn); 
		if($highestRow >=50){
			set_time_limit(0);
		}
		$hasHeader = $this->data['NewsletterSendlist']['import_first_row'];
		$cols = $this->data['NewsletterSendlist']['cols'];
		if(!empty($this->data['NewsletterSendlist']['list_id'])){
			$list_id = $this->data['NewsletterSendlist']['list_id'];
		}else{
			$data = array(
				'active' => true,
				'title' => $this->data['NewsletterSendlist']['new_list'],
			);
			$this->NewsletterSendlist->create();
			if($this->NewsletterSendlist->save($data)){
				$list_id = $this->NewsletterSendlist->id;
			}else{
				return false;
			}
		}
		$skipped = 0;
		$added = 0;
		$error = 0;
		for ($row = 2 - $hasHeader; $row <= $highestRow; ++$row) {
			//$this->log("row");
			$data = array(
				'active' => true,
				'sendlist_id' => $list_id,
			);
			foreach($cols as $col => $val){
				if(!empty($val)){
					$cell = $objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
					$data[$val] = trim($cell);
				}
			}
			if(!empty($data['email'])){
				if(!empty($data['first_name'])){
					$data['name'] = $data['first_name'];
				}
				if(!empty($data['last_name'])){
					$data['name'] = trim($data['name'] . ' ' . $data['last_name']);
				}
				$data['email'] = strtolower($data['email']);
				$exists = $this->NewsletterSendlist->NewsletterEmail->find('first',array('conditions'=>array('sendlist_id' => $list_id, 'email' => $data['email'])));
				if(empty($exists)){
					//debug($data);
					$this->NewsletterSendlist->NewsletterEmail->create();
					if($this->NewsletterSendlist->NewsletterEmail->save($data)){
						$added++;
					}else{
						$error++;
					}
				}else{
					$skipped++;
				}
			}else{
				$error++;
			}
			if($highestRow >= 50 && $row % 10 == 0){
				echo $row.'/'.$highestRow.'<br />';
				@ob_flush();
				flush();
				if($highestRow >= 80){
					usleep(100);
				}
			}
		}
		$this->Session->setFlash(sprintf(__('%s emails added, %s duplicated emails skipped, %s invalid emails.', true), $added, $skipped, $error));
		if($highestRow >= 50){
			echo '<script type="text/javascript">
				<!--
				window.location = "'.Router::url(array('action'=>'index')).'"
				//-->
				</script>';
			exit();
		}else{
			$this->redirect(array('action'=>'index'));
		}
	}
	
	function admin_view($id = null) {
		if (!$id) {
			$this->Session->setFlash(__('Invalid NewsletterSendlist.', true));
			$this->redirect(array('action'=>'index'));
		}
		$this->set('newsletterSendlist', $this->NewsletterSendlist->read(null, $id));
	}

	function admin_xls($id = null) {
		if (!$id) {
			$this->Session->setFlash(__('Invalid NewsletterSendlist.', true));
			$this->redirect(array('action'=>'index'));
		}
		if($this->Funct->isTableSendlist($id)){
			$tableSendlist = $this->Funct->getTableSendlistID($id,true);
			$Model = $tableSendlist['modelClass'];
			$Model->recursive = -1;
			$findOptions = $this->Funct->tabledEmailGetFindOptions($tableSendlist,!$tableSendlist['showInnactive']);
			$mails = $Model->find('all',$findOptions);
			$newsletterEmails = array();
			foreach($mails as $mail){
				$newsletterEmails[] = $this->Funct->tabledEmailGetFields($mail,$tableSendlist,'NewsletterEmail');
			}
		}else{
			$newsletterEmails = $this->NewsletterSendlist->NewsletterEmail->find('all',array('conditions'=>array('NewsletterEmail.sendlist_id'=>$id)));
		}
		$sendlist = $this->NewsletterSendlist->read(null, $id);
		
		if(!empty($newsletterEmails)){
			App::import('Vendor', 'Newsletter.PHPExcel',array('file' => 'PHPExcel.php'));
			App::import('Vendor', 'Newsletter.PHPExcel_Writer_Excel2007',array('file' => 'PHPExcel/Writer/Excel2007.php'));
			
			/////// set document properties ///////
			$objPHPExcel = new PHPExcel();
			$title = $sendlist['NewsletterSendlist']['title'];
			if(strlen($title) > 30){
				$title = substr($title,0,30);
			}
			$objPHPExcel->getProperties()->setTitle($title);
			$objPHPExcel->getProperties()->setSubject($title);
			$objPHPExcel->getProperties()->setDescription(str_replace(
				array('%title%','%id%'),
				array($sendlist['NewsletterSendlist']['title'],$sendlist['NewsletterSendlist']['id']),
				__('Export of the "%title%" sendlist (ID %id%).',true)
			));
			$objPHPExcel->setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet()->setTitle($title);
			
			/////// set headers ///////
			$i = 0;
			$exclude = array('id','data','sendlist_id','modified');
			foreach($newsletterEmails[0]['NewsletterEmail'] as $key => $val){
				if(!in_array($key,$exclude)){
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i,1, Inflector::humanize($key));
					$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($i,1)->getFont()->setBold(true);
					$objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($i)->setAutoSize(true);
					$i++;
				}
			}
			
			/////// write data ///////
			foreach($newsletterEmails as $row => $mail){
				$i = 0;
				foreach($mail['NewsletterEmail'] as $key => $val){
					if(!in_array($key,$exclude)){
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i,$row+2, $val);
						$i++;
					}
				}
			}
			
			/////// output ///////
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'.Inflector::slug($sendlist['NewsletterSendlist']['title']).'.xlsx"');
			header('Cache-Control: max-age=0');
			
			$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
			$objWriter->save('php://output');
		}else{
			$this->Session->setFlash(__('The NewsletterSendlist is empty, nothing to export.', true));
			$this->redirect(array('action'=>'index'));
		}
		
		//debug( $newsletterEmails);
		//$this->render(false);
	}
	

	function admin_add() {
		if (!empty($this->data)) {
			$this->NewsletterSendlist->create();
			if ($this->NewsletterSendlist->save($this->data)) {
				$this->Session->setFlash(__d('newsletter','The NewsletterSendlist has been saved', true));
				$this->redirect(array('action'=>'index'));
			} else {
				$this->Session->setFlash(__d('newsletter','The NewsletterSendlist could not be saved. Please, try again.', true));
			}
		}
	}

	function admin_edit($id = null) {
	
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(__d('newsletter','Invalid NewsletterSendlist', true));
			$this->redirect(array('action'=>'index'));
		}
		if (!empty($this->data)) {
			if ($this->NewsletterSendlist->save($this->data)) {
				$this->Session->setFlash(__d('newsletter','The NewsletterSendlist has been saved', true));
				$this->redirect(array('action'=>'index'));
			} else {
				$this->Session->setFlash(__d('newsletter','The NewsletterSendlist could not be saved. Please, try again.', true));
			}
		}
		if (empty($this->data)) {
			$this->data = $this->NewsletterSendlist->read(null, $id);
		}
		
		$this->set('tabled',$this->Funct->isTableSendlist($id));
	}

	function admin_up($id = null) {
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(__d('newsletter','Invalid NewsletterSendlist', true));
		}
		$this->NewsletterSendlist->move(-1,$id);
		$this->redirect(array('action'=>'index'));
		//$this->render(false);
	}
	function admin_down($id = null) {
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(__d('newsletter','Invalid NewsletterSendlist', true));
		}
		$this->NewsletterSendlist->move(1,$id);
		$this->redirect(array('action'=>'index'));
	}

	function admin_delete($id = null) {
		if (!$id) {
			$this->Session->setFlash(__d('newsletter','Invalid id for NewsletterSendlist', true));
			$this->redirect(array('action'=>'index'));
		}
		if ($this->NewsletterSendlist->delete($id)) {
			$this->Session->setFlash(__d('newsletter','NewsletterSendlist deleted', true));
			$this->redirect(array('action'=>'index'));
		}
	}

}
?>