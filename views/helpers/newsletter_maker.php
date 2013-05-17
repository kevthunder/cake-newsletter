<?php
class NewsletterMakerHelper extends AppHelper {
	var $helpers = array('html','Form','Photo','Multimedia.Multimedia');
	
	//////////////////// internal ////////////////////
	function beforeRender(){
		$view =& ClassRegistry::getObject('view');
		$this->view = $view;	
		$this->newsletter_box = $view->getVar('newsletter_box');
		$this->newsletter = $view->getVar('newsletter');
       	$this->box_element = $this->newsletter_box["NewsletterBox"]["template"];
		$this->box_id = $this->newsletter_box["NewsletterBox"]["id"];
		//$this->data = $view->data;
		$this->boxes_by_zone = $view->getVar('boxes_by_zone');
		
		$this->Session = new CakeSession();
		$this->Session->start();
		//debug('test');
	}
	
	function showBox($box,$options=array()){
		$defOpt = array(
			'wrapper' => array(
				'tag' => 'div',
				'editMode' => empty($options['wrapper']),
				'attr' => array(),
			),
			'indent' => '',
		);
		$opt = Set::merge($defOpt,$options);
		
		$editMode = $this->inEditMode();
		$result_html = '';
		
		if($editMode && $opt['wrapper']){
			if(!empty($box['NewsletterBox']['id'])){
				$opt['wrapper']['attr']['id'] = 'box'.$box['NewsletterBox']['id'];
				$opt['wrapper']['attr']['boxid'] = $box['NewsletterBox']['id'];
			}
			$opt['wrapper']['attr']['class'][] = 'newsletter_box';
		}elseif($opt['wrapper']['editMode']){
			$opt['wrapper'] = false;
		}
		if($opt['wrapper']) $result_html .= $opt['indent'].'<'.$opt['wrapper']['tag'].$this->_parseAttributes($opt['wrapper']['attr']).'>';
		$element = array('newsletter_box/'.$this->newsletter['Newsletter']['template'].'/'.$box['NewsletterBox']['template'],'newsletter_box/'.$box['NewsletterBox']['template']);
		
		$slug = '';
		if(!empty($box['NewsletterBox']['data']['title'])){
			$slug = strtolower(Inflector::slug($box['NewsletterBox']['data']['title'])).'_';
		}
		
		if(!empty($box['NewsletterBox']['id'])){
			$result_html .= '<a name="'.$slug.$box['NewsletterBox']['id'].'"></a>';
		}
		
		$result_html .= $this->view->element($element, array("newsletter_box" => $box));//,'plugin' => 'none'
		$result_html .= $this->boxFooter($box);
		if($opt['wrapper']) $result_html .= $opt['indent'].'</'.$opt['wrapper']['tag'].'>'."\n";
		
		return $result_html;
	}
	
	function boxFooter($box=NULL){
		if(!$box){
			$box = $this->newsletter_box;
		}
		
		$result_html  = '';
		if($this->inEditMode()){
			$zoneOpt = $this->getZoneOpt($box['NewsletterBox']['zone']);
			if(!empty($box['NewsletterBox']['id']) && $zoneOpt['ordered']){
				$box_id = $box['NewsletterBox']['id'];
				$result_html .= $this->Form->input('NewsletterBox.'.$box_id.'.id',array('id'=>'NewsletterBoxId','value'=>$box_id));
				$order = 0;
				if(isset($box['NewsletterBox']['order'])){
					$order = $box['NewsletterBox']['order'];
				}
				$result_html .= $this->Form->input('NewsletterBox.'.$box_id.'.order',array('id'=>'NewsletterBoxOrder','value'=>$order,'type'=>'hidden'));
				$result_html .= $this->Form->input('NewsletterBox.'.$box_id.'.zone',array('id'=>'NewsletterBoxZone','value'=>$box['NewsletterBox']['zone'],'type'=>'hidden'));
			}
			$result_html .= $this->boxActions($zoneOpt);
		}
		return $result_html;
	}
	function boxActions($options = array()){
		$defOpt = array(
			'edit' => true,
			'delete' => true,
		);
		$opt = Set::merge($defOpt,$options);
	
		$result_html  = '<div class="box_actions">'."\n";
		$result_html .= '	<ul>'."\n";
		if($opt['edit']) $result_html .= '		<li><a class="edit_box_link">'.__d('newsletter','Edit', true).'</a></li>'."\n";
		if($opt['delete']) $result_html .= '		<li><a class="del_box_link">'.__d('newsletter','Delete', true).'</a></li>'."\n";
		$result_html .= '	</ul>'."\n";
		$result_html .= '</div>'."\n";
		return $result_html;
	}
	
	function _boxSeparator($opt){
		$defaultOpt = array(
			'tr' => array(
				'class'=>array('nltr_sep'),
			),
			'td' => array(),
			'content' => '&nbsp',
		);
		
		if(!is_array($opt)){
			$opt = array('content'=>$opt);
		}
		
		App::import('Lib', 'Newsletter.SetMulti');
		$opt = SetMulti::merge2($defaultOpt,$opt);
		
		$result_html  = '<tr'.$this->_parseAttributes($opt['tr']).'>'."\n";
		$result_html .= '	<td'.$this->_parseAttributes($opt['td']).'>'."\n";
		$result_html .= '		'.$opt['content']."\n";
		$result_html .= '	</td>'."\n";
		$result_html .= '</tr>'."\n";
		return $result_html;
		
	}
	
	function _parseAttributes($attr, $exclude = null, $insertBefore = ' ', $insertAfter = null){
		if(isset($attr['class']) && is_array($attr['class'])){
			$attr['class'] = implode(' ', $attr['class']);
		}
		return parent::_parseAttributes($attr, $exclude, $insertBefore, $insertAfter);
	}
	
	
	function setZoneOpt($id,$options=array()){
		$opt = Set::merge(NewsletterConfig::getDefZoneOpt(),$options);
		$opt['boxList'] = Set::normalize($opt['boxList']);
		
		$this->zones[$id] = $opt;
		$this->Session->write('EditedNewsletter.'.$this->newsletter['Newsletter']['id'].'.zone.'.$id, $opt);
	}
	
	function getZoneOpt($id){
		if(!empty($this->zones[$id])){
			return $this->zones[$id];
		}else{
			$session = $this->Session->read('EditedNewsletter.'.$this->newsletter['Newsletter']['id'].'.zone.'.$id);
			if(!empty($session)) {
				$this->zones[$id] = $session;
				return $session;
			}
		}
		return NewsletterConfig::getDefZoneOpt();
	}
	
	//////////////////// Newsletter layout ////////////////////
	function inEditMode(){
		return $this->params['controller'] == 'newsletter' && ($this->params['action'] == 'admin_edit' || $this->params['action'] == 'admin_preview' || $this->params['action'] == 'admin_add_box' || $this->params['action'] == 'admin_edit_box' || $this->params['action'] == 'admin_view_box');
	}
	function column($id,$options=array()){
		$editMode = $this->inEditMode();
		$used_opt = array('allowedBox','deniedBox','table','tbody','trHeader','th','tr','td','separator','separatorVertical');
		
		$boxList = NULL;
		if($editMode){
			$box_elements = array_keys($this->view->getVar('box_elements'));
			if(isset($options['allowedBox'])){
				if(!is_array($options['allowedBox'])){
					$options['allowedBox'] = array($options['allowedBox']);
				}
				$boxList = array_intersect($box_elements,$options['allowedBox']);
			}
			if(isset($options['deniedBox'])){
				if(!is_array($options['deniedBox'])){
					$options['deniedBox'] = array($options['deniedBox']);
				}
				$boxList = array_diff($box_elements,$options['deniedBox']);
			}
			$this->setZoneOpt($id,array('boxList'=>$boxList));
		}
		
		//== elements attributes ==
		$elements = array(
			'table'=>array(
				'defaultAttr' => array(
					'cellspacing' => 0,
					'cellpadding' => 0,
				),
				'overrideAttr' => array(
					'id'=>'nltr_'.$id,
					'zoneid'=>$id,
					'class'=>array('nltr_column','nltr_container'),
				)
			),
			'tbody'=>array(),
			'trHeader'=>array(
				'overrideAttr' => array(
					'class'=>array('header_row'),
				)
			),
			'th'=>array(),
			'tr'=>array(
				'overrideAttr' => array(
					'class'=>array('box_row'),
				)
			),
			'td'=>array(
			)
		);
		if(!is_null($boxList)){
			$elements['table']['overrideAttr']['boxlist'] = implode(';', $boxList);
		}
		
		if(!isset($options['table'])){
			$options['table'] = array();
		}
		$options['table'] = Set::merge($options['table'],array_diff_key($options,array_flip($used_opt)));
		
		
		//== merge attributes and options ==
		foreach($elements as $name => &$elem){
			$elem['attr'] = array();
			if(isset($elem['defaultAttr'])){
				$elem['attr'] = Set::merge($elem['attr'],$elem['defaultAttr']);
			}
			if(isset($options[$name])){
				$elem['attr'] = Set::merge($elem['attr'],$options[$name]);
			}
			if(isset($elem['attr']['class'])){
				$elem['attr']['class'] = (array)$elem['attr']['class'];
			}
			if(isset($elem['overrideAttr'])){
				$elem['attr'] = Set::merge($elem['attr'],$elem['overrideAttr']);
			}
		}
		//debug($elements);
		
		//== make output ==
		if($editMode || !empty($this->boxes_by_zone[$id])){
			$result_html = '<table'.$this->_parseAttributes($elements['table']['attr']).'><tbody'.$this->_parseAttributes($elements['tbody']['attr']).'>'."\n";
			if($editMode){
				$result_html .= '	<tr'.$this->_parseAttributes($elements['trHeader']['attr']).'>'."\n";
				$result_html .= '	<th'.$this->_parseAttributes($elements['th']['attr']).'><a class="add_box_link newsletter_bt">'.__d('newsletter','Add content', true).'</a></th>'."\n";
				$result_html .= '	</tr>'."\n";
			}
			//debug($this->boxes_by_zone);
			if(isset($this->boxes_by_zone[$id])){
				$i = 0;
				foreach($this->boxes_by_zone[$id] as $box){
					$result_html .= '	<tr'.$this->_parseAttributes($elements['tr']['attr']).'>'."\n";
					$attr = $elements['td']['attr'];
					
					$result_html .= $this->showBox($box,array(
						'wrapper' => array(
							'tag' => 'td',
							'attr' => $elements['td']['attr'],
						),
						'indent' => '		',
					));
					
					$result_html .= '	</tr>'."\n";
					
					if($i+1 < count($this->boxes_by_zone[$id]) && !empty($options['separator'])){
						$result_html .= $this->_boxSeparator($options['separator']);
					}
					$i++;
				}
			}
			$result_html .= '</tbody></table>'."\n";
			return $result_html;
		}
		return null;
	}
	function row($id){
		return '<table id="nltr_'.$id.'" class="nltr_row nltr_container" cellspacing="0" cellpadding="0"></table>';
	}
	function single($id,$boxTemplate,$options=array()){
		$defOpt = array(
			'data' => array(),
		);
		if(!count(array_intersect_key($defOpt,$options))){
			$options = array('data' => $options);
		}
		$opt = array_merge($defOpt,$options);
		
		$id = 'single--'.$id;
		
		$editMode = $this->inEditMode();
		if($editMode){
			$this->setZoneOpt($id,array(
				'ordered'=>false,
				'delete'=>false,
				'boxList'=>array($boxTemplate=>array('data'=>$opt['data'])),
			));
		}
		
		$box = array();
		if(!empty($this->boxes_by_zone[$id][0])){
			$box = $this->boxes_by_zone[$id][0];
		}else{
			$box = array('NewsletterBox'=>array(
				'newsletter_id' => $this->newsletter['Newsletter']['id'],
				'zone' => $id,
				'template' => $boxTemplate,
				'data' => $opt['data'],
			));
			if(!empty($opt['data']['file'])){
				$box['NewsletterBox']['file'] = $opt['data']['file'];
			}
		}
		
		
		return $this->showBox($box,array(
			'wrapper' => array(
				'editMode'=> true,
				'attr' => array(
					'class' => array('nltr_single'),
					'zoneid'=>$id,
				),
			),
		));
	}
	function langSwitch($options = array()){
		$defOpt = array(
			'beforeLink' => '',
			'afterLink' => '',
			'separator' => ' - ',
		);
		$local = array_keys($defOpt);
		$opt = array_merge($defOpt,$options);
		$html = '';
		$langs = NewsletterConfig::load('langs');
		$i = 0;
		foreach($langs as $key => $val){
			if($key != $this->newsletter['Newsletter']['lang'] && !empty($this->newsletter['Newsletter']['associated'][$key])){
				if($i != 0){
					$html .= $opt['separator'];
				}
				$url = array(
					'plugin'=>'newsletter', 
					'controller'=>'newsletter', 
					'action'=>'view', 
					$this->newsletter['Newsletter']['associated'][$key], 
					'admin' => false
				);
				$html .= '<a href="'.$this->url($url).'"'.$this->_parseAttributes($opt, $local).'>'.$opt['beforeLink'].$val.$opt['afterLink'].'</a>';
				$i++;
			}
		}
		return $html;
	}
	function tableOfContents($zones=null,$options=array()){
		$defOpt = array(
			'group' => '<ul>%items%</ul>',
			'item' => '<li><a href="%anchor%">%title%</a></li>',
			'titleGroup' => '<ul>%groups%</ul>',
			'title' => '<li><p>%title%</p>%items%</li>',
		);
		$opt = array_merge($defOpt,$options);
		if(empty($zones)){
			$zones = array_keys($this->boxes_by_zone);
		}
		if(!is_array($zones)){
			$zones = array($zones);
		}
		$named = !set::numeric(array_keys($zones));
		$out = '';
		foreach($zones as $key => $id){
			$links = '';
			if(!empty($this->boxes_by_zone[$id])){
				foreach($this->boxes_by_zone[$id] as $box){
					if(!empty($box['NewsletterBox']['data']['title'])){
						$anchor = '#'.strtolower(Inflector::slug($box['NewsletterBox']['data']['title'])).'_'.$box['NewsletterBox']['id'];
						$replace = array(
							'%anchor%'=>$anchor,
							'%title%'=>$box['NewsletterBox']['data']['title'],
						);
						$links .= str_replace(array_keys($replace),array_values($replace),$opt['item']);
					}
				}
				if(!empty($links)){
					$links = str_replace('%items%',$links,$opt['group']);
				}
			}
			if(!empty($links) && $named){
				$replace = array(
					'%title%'=>$key,
					'%items%'=>$links,
				);
				$out .= str_replace(array_keys($replace),array_values($replace),$opt['title']);
			}else{
				$out .= $links;
			}
		}
		if(!empty($out) && $named){
			$out = str_replace('%groups%',$out,$opt['titleGroup']);
		}
		return $out;
	}
	
	function inMakeBox(){
		return $this->params['controller'] == 'newsletter' && ($this->params['action'] == 'view_box' || $this->params['action'] == 'admin_add_box' || $this->params['action'] == 'admin_edit_box' || $this->params['action'] == 'admin_view_box');
	}
	function showData($name){
		if($this->inMakeBox()){
			return '<div id="'.$this->box_element.'__'.$name.'"></div>';
		}
	}
	function counterImg($file=NULL,$options=array()){
		$src = $this->html->url(array('plugin' => 'newsletter', 'controller' => 'newsletter', 'action' => 'counter', 'admin'=>false),true);
		$src .= '/%sended_id%';
		if($file){
			$src .= '/'.urlencode(str_replace('/','>',$file));
		}else{
			$options['height'] = 1;
			$options['width'] = 1;
		}
		$attributes = '';
		foreach($options as $opt_name => $opt_val){
			$attributes .= ' '.$opt_name.' = "'.$opt_val.'"';
		}
		$result_html = '<img src="'.$src.'" '.$attributes.'/>';
		$this->view->set('has_counter_img',true);
		return $result_html;
	}
	
	//////////////////// Newsletter layout & boxes ////////////////////
	function arrayToTime($options){
		$default_options = array(
			'year'=> date("Y"), 
			'month'=> date("n"),
			'day'=> date("j"),
			'hour'=>date("H"),
			'min'=> date("i"),
			'sec'=> date("s")
		);
		$options = array_merge($default_options,$options);
		mktime($options['hour'],$options['min'],$options['sec'],$options['month'],$options['day'],$options['year']);
	}
	function filterRichtext($text, $options = array()){
		$defOpt = array(
			'pToBr' => false,
			'wrap' => array(),	// array(
								//   'selector'=>'<font color="#393939">%s</font>', 
								// )
		);
		$opt = array_merge($defOpt,$options);
		//filter urls
		$findUrl = '/=["\']'.str_replace('/','\/',$this->html->url('/')).'([-\/_=?&%.:#a-zA-Z0-9]*)["\']/';
		//debug($findUrl);
		while(preg_match($findUrl,$text,$matches,PREG_OFFSET_CAPTURE)){
			if(preg_match('/<img[^>]*$/',substr($text,0,$matches[0][1]))){
				$fullUrl = $this->html->url('/'.$matches[1][0],true);
			}else{
				$fullUrl = $this->url('/'.$matches[1][0]);
			}
			$text = substr($text,0,$matches[0][1]).'="'.$fullUrl.'"'.substr($text,$matches[0][1]+strlen($matches[0][0]));
			//debug($matches);
		}
		if(!empty($opt['wrap'])){
			App::import('Vendor', 'Newsletter.simple_html_dom_node',array('file'=>'simple_html_dom.php'));
			
			$html = str_get_html($text);

			foreach($opt['wrap'] as $selector => $wrapper){
				foreach($html->find($selector) as $element){
					$element->innertext = str_replace('%s',$element->innertext,$wrapper);
				}
			}
			$text = (string)$html;
		}
		if($opt['pToBr']){
			$text = $this->pToBr($text);
		}
		return $text;
	}
	function pToBr($text){
		return str_replace(array("<p>","</p>"),"",preg_replace("/<\/p>(?!$)/","<br /><br />",$text));
	}
	function url($url){
		$base_url = '';
		if(is_array($url)){
			$url['base'] = false;
			$url['admin'] = false;
			$base_url = $this->html->url($url);
		}else{
			$base_url = str_replace($this->html->url('/',true),'/',$url);
		}
		$final_url = $this->html->url(array('plugin' => 'newsletter', 'controller' => 'newsletter', 'action' => 'redir', 'admin'=>false),true);
		//$encoded_link = urlencode(str_replace(array('/',':','?'),array('>',';','!'),$base_url));
		//$encoded_link = urlencode(urlencode(urlencode(urlencode($base_url))));
		$encoded_link = str_replace('/','-',base64_encode($base_url));
		$final_url .= '/'.$encoded_link;
		if(!$this->inEditMode()){
		$final_url .= '/%sended_id%';
		}
		return $final_url;
	}
	function unsubscribeUrl(){
		return $this->html->url(array('plugin'=>'newsletter', 'controller'=>'newsletter', 'action'=>'unsubscribe', '%sended_id%', 'admin' => false),true);
	}
	function viewUrl(){
		return $this->url(array('plugin'=>'newsletter', 'controller'=>'newsletter', 'action'=>'view', $this->newsletter["Newsletter"]["id"], 'admin' => false));
	}
	function selfSendingUrl(){
		return $this->url(array('plugin'=>'newsletter', 'controller'=>'newsletter_sendings', 'action'=>'add', $this->newsletter["Newsletter"]["id"],'%sended_id%', 'admin' => false));
	}
	function title(){
		return $this->newsletter["Newsletter"]["title"];
	}
	function date($format = "jS F Y"){
		return date_($format,strtotime($this->newsletter["Newsletter"]["date"]));
	}
	
	//////////////////// Box Editor ////////////////////
	function createEditForm(){
		$result_html  = '<div id="'.$this->box_element.'__editForm" class="edit_form" boxid="'.$this->box_id.'">';
		//$result_html .= $this->Ajax->form('edit','post',array('model'=>'NewsletterBox','update'=>'box'.$this->box_id));
		$form_url = array('plugin'=>'newsletter','controller'=>'newsletter','action'=>'edit_box',$this->box_id);
		$result_html .= $this->Form->create("NewsletterBox",array('enctype' => 'multipart/form-data', 'url'=>$form_url));
		$result_html .= $this->Form->input('id',array('value'=>$this->box_id));
		return $result_html;
	}
	function multimedia(){
		$this->Multimedia->display('NewsletterBox.multimedia');
	}
	function editInput($name,$options=array()){
		if(!is_array($options)){
			$options=array();
		}
		$target = explode('.',$name);
		/*if(!isset($options['value']) && isset($this->newsletter_box["NewsletterBox"]["data"][$name])){
			$options['value'] = $this->newsletter_box["NewsletterBox"]["data"][$name];
		}
		if(!isset($options['label'])){
			$options['label'] = ucfirst($target[count($target)-1]);
		}
		$options['name'] = 'data[NewsletterBox][data]';
		foreach($target as $part){
			$options['name'] .= '['.$part.']';
		}
		if(!isset($options['id'])){
			$options['id'] = 'NewsletterBoxData';
			foreach($target as $part){
				$options['id'] .= ucfirst($part);
			}
		}*/
		$type = '';
		if(!isset($options['type'])){
			if($target[count($target)-1]=='id'){
				$options['type'] = 'hidden';
			}elseif(isset($options['options'])){
				$options['type'] = 'select';
			}else{
				$options['type'] = 'text';
			}
		}else{
			$type = $this->Form->input('NewsletterBox.data.types'.'.'.$name,array('type'=>'hidden','value'=>$options['type']));
			if($options['type']=='tinymce') {
				$options['type'] = 'textarea';
				if(!isset($options['class'])){
					$options['class'] = '';
				}else{
					$options['class'] .= ' ';
				}
				$options['class'] .= 'tinymce';
			}
		}
		return $type.$this->Form->input('NewsletterBox.data'.'.'.$name,$options);//'target'=>$this->box_element.'__'.$name
	}
	function editFileInput($name,$options=array()){
		if(!is_array($options)){
			$options=array();
		}
		if(isset($options['between'])){
			$between = $options['between'];
		}else{
			$between = "";
		}
		if(isset($this->newsletter_box["NewsletterBox"]["file"][$name])){
			$file = $this->newsletter_box["NewsletterBox"]["file"][$name];
			if($file["type"]=='image'){
				$between .= '<img src="'.$this->Photo->path($file['path'],$file["file"], array('size'=>'50x50', 'method'=>'resize')).'">';
			}
			$between .= '<p>Fichier actuel : '.$file['file'].'</p>';
			$between .= $this->Form->input($name, array('type' => 'checkbox', 'id'=>'NewsletterBox'.Inflector::camelize($name).'Del', 'label' => 'Effacer le fichier','name' => 'data[NewsletterBox][file]['.$name.'][del]'));
		}
		$options['between'] = $between;
		$options['type'] = 'file';
		$options['name'] = 'data[NewsletterBox][file]['.$name.']';
		return $this->Form->input($name,$options);
	}
	function editEntryFinder($model,$options=array()){
		if (strpos($model, '.') !== false) {
			list($plugin, $modelName) = explode('.', $model);
		}else{
			$modelName = $model;
		}
		if(isset($options['onLoadData'])){
			$options['div']['onloaddata'] = $options['onLoadData'];
			unset($options['onLoadData']);
		}
		if(isset($options['map'])){
			$options['div']['map'] = json_encode($options['map']);
			unset($options['map']);
		}
		$options['div']['model'] = $model;
		if(!isset($options['div']['class'])){
			$options['div']['class'] = array('input','text');
		}
		$options['div']['class'] = array_merge((array)$options['div']['class'],array('entry_finder'));
		$options['div']['class'] = implode(' ',$options['div']['class']);
		
		if(!isset($options['class'])){
			$options['class'] = array();
		}
		$options['class'] = array_merge((array)$options['class'],array('id_input'));
		$options['class'] = implode(' ',$options['class']);
		
		if(!isset($options['after'])){
			$options['after'] = '';
		}
		$searchUrl = $this->html->url(array('plugin'=>'newsletter','controller' => 'newsletter_assets', 'action' => 'popup_entry_search',$model));
		$bt = '<a class="bt_load newsletter_bt">'.__d('newsletter',"Load Data",true).'</a>';
		$bt .= '<a class="bt_search newsletter_bt" href="'.$searchUrl.'">'.__d('newsletter',"Search",true).'</a>';
		$bt .= '<div class="loading" style="display:none"></div>';
		$options['after'] = $bt.$options['after'].'<br style="clear:both" />';
		
		if(!isset($options['field_name'])){
			$options['field_name'] = Inflector::underscore($modelName."_id");
		}
		
		if(empty($options['type']) && empty($options['options'])){
			$values = $this->view->getVar(Inflector::tableize($modelName));
			if(!empty($values) && empty($options['type']) && empty($options['options'])){
				$options['type'] = 'select';
				$options['options'] = $values;
			}
		}
		
		echo $this->editInput($options['field_name'],$options);
	}
	function editEntriesSelect($name,$entries,$options=array()){
		if(!is_array($options)){
			$options=array();
		}
		if(!isset($options['pagin'])){ 
			$options['pagin'] = true;
		}
		if(!isset($options['paginLimit']) && $options['pagin']){
			$options['paginLimit'] = 10;
		}
		if(!isset($options['paginSeparator'])){
			$options['paginSeparator'] = ' - ';
		}
		$result_html  = '<div'.(isset($options['class'])?' class="'.$options['class'].'"':'').'>'."\n";
		$result_html .= '	<label>'.(isset($options['label'])?$options['label']:ucfirst($name)).'</label>'."\n";
		$result_html .= '	<div id="entries_list">'."\n";
		$pagin = '';
		if($options['pagin']){
			if(isset($options['paginCur'])){
				$cur_page = $options['paginCur'];
			}else{
				$cur_page = 1;
			}
			if(isset($options['paginNb'])){
				$nb_page = $options['paginNb'];
			}elseif(isset($options['paginLimit'])){
				$nb_page = ceil(count($entries)/$options['paginLimit']);
			}else{
				$nb_page = 1;
			}
			$pagin .= '<div class="pagin">'."\n";
			
			$pagin .= '	';
			for($i=1;$i<=$nb_page;$i++){
				if($i!=1){
					$pagin .= $options['paginSeparator'];
				}
				$pagin .= '<span class="pagin_page'.($i==$cur_page?' cur':'').'" page="'.$i.'">'.$i.'</span>';
				
			}
			$pagin .= '</div>'."\n";
		}
		$i = 0;
		if(isset($this->newsletter_box["NewsletterBox"]["data"][$name])){
			foreach($this->newsletter_box["NewsletterBox"]["data"][$name] as $entry){
				$result_html .= makeEntry($entry,$name,$options,true,$i,$this);
			}
		}
		$result_html .= $pagin;
		$j = 0;
		$page = 1;
		foreach($entries as $entry){
			if($options['pagin'] && $j%$options['paginLimit']==0){
				$result_html .= '		<div id="page'.$page.'" class="page'.($page==$cur_page?' cur_page':'').'">'."\n";
			}
			$result_html .= makeEntry($entry,$name,$options,false,$i,$this);
			if($options['pagin'] && $j%$options['paginLimit']==$options['paginLimit']-1){
				$result_html .= '		</div>'."\n";
				$page++;
			}
			$j++;
		}
		if($options['pagin'] && $j%$options['paginLimit']!=0){
			$result_html .= '		</div>'."\n";
		}
		$result_html .= $pagin;
		$result_html .= '	</div>'."\n";
		$result_html .= '</div>'."\n";
		
		return $result_html;
	}
    function endEditForm(){
		//$result_html  = $this->Ajax->submit("Submit");
		$result_html  = $this->Form->button(__d('newsletter','Submit',true),array("class"=>'submit_edit_form'));
		$result_html .= $this->Form->end();
		$result_html .= '</div>';
		return $result_html;
	}
}
function makeEntry($entry,$name,$options,$active,&$i,$This){
	$result_html = '';
	$result_html .= '		<div id="news_'.$i.'" class="entry'.($i%2==1?' alt':'').'">'."\n";
	if(isset($options['displayField'])){
		$title = $entry[$options['displayField']];
	}elseif(isset($entry['title'])){
		$title = $entry['title'];
	}elseif(isset($entry['id'])){
		$title = $entry['id'];
	}else{
		$title = $i;
	}
	$result_html .= '			'.$This->Form->input($name."_".$i."_active",array('type'=>'checkbox','label'=>false,'checked'=>$active,'class'=>'checkbox','after'=>'<div class="title">'.$title.'</div><br style="clear:both;">'))."\n";
	$result_html .= '			<div style="clear:both;"/>'."\n";
	$result_html .= '			<div class="data">'."\n";
	unset($entry['active']);
	foreach($entry as $fieldName => $field){
		$inputOpts = array('value'=>$field);
		if(!$active){
			$inputOpts['disabled'] = 'disabled';
		}
		if(isset($options['fieldsOptions'][$fieldName])){
			$inputOpts = array_merge($inputOpts,$options['fieldsOptions'][$fieldName]);
		}
		$result_html .= '			'.$This->editInput($name.".".$i.".".$fieldName,$inputOpts)."\n";
	}
	$result_html .= '			</div>'."\n";
	$result_html .= '		</div>'."\n";
	$i++;
	return $result_html;
}
?>