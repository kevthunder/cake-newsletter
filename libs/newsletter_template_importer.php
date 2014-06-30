<?php
class NewsletterTemplateImporter extends Object {
	/*
		App::import('Lib', 'Newsletter.NewsletterTemplateImporter');
	*/
	
	public static function checkRequirements(){
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
		return $available;
	}
	
	
	var $file = "";
	var $title = "";
	
	function __construct($file,$title) 
    { 
		$this->file = $file;
		$this->title = $title;
	}
	
	
	function process(&$error = null){
		$file = $this->file;
		$name = $this->title;
		
		$zip = new ZipArchive;
		$res = $zip->open($file);
		if (!$res) {
			$error = __d('newsletter','Cant read archive', true);
			return false;
		}
		$newsletterFileName = strtolower(Inflector::slug($name));
		
		///////// create needed folder /////////
		if(!file_exists(WWW_ROOT.'img'.DS.'newsletter'.DS.$newsletterFileName)){
			if(!mkdir(WWW_ROOT.'img'.DS.'newsletter'.DS.$newsletterFileName,  0777)){
				$error = __d('newsletter','Cant create img folder', true);
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
			$error = __d('newsletter','Cant find main html file', true);
			return false;
		}
		$content = $zip->getFromName($contentFile);
		if(empty($content)){
			$error = __d('newsletter','Cant read main html file', true);
			return false;
		}
		$content = $this->formatContent($content,$newsletterFileName);
		file_put_contents (APP.'views'.DS.'elements'.DS.'newsletter'.DS.$newsletterFileName.'.ctp' , $content);
		
		
		///////// create config file /////////
		$pluginPath = App::pluginPath('Newsletter');
		ob_start();
		include($pluginPath.'vendors'.DS.'config_template.php');
		$configFile = ob_get_clean();
		file_put_contents (APP.'config'.DS.'plugins'.DS.'newsletter'.DS.$newsletterFileName.'.php' , $configFile);
		
		
		///////// extract images /////////
		$this->zipExtractToFlat($zip,WWW_ROOT.'img'.DS.'newsletter'.DS.$newsletterFileName,$images);
		
		$zip->close();
		return true;
	}
	
	function formatContent($content,$newsletterFileName){
		$SpecialLinks = array(
			'unsubcribeMatch' => array(
				'find' => '/unsubscribe|désabonner/',
				'link' => '<?php echo $this->NewsletterMaker->unsubscribeUrl(); ?>'
			),
			'webversionMatch' => array(
				'find' => '/pas(\sà)?\slire|can(\'t| not)\sread/',
				'link' => '<?php echo $this->NewsletterMaker->viewUrl(); ?>'
			)
		);
		foreach($SpecialLinks as $opt){
			if(preg_match($opt['find'],$content,$matches,PREG_OFFSET_CAPTURE)){
				$pos = $matches[0][1];
				if(preg_match_all("/<(p|a|td)/",substr($content,0,$pos),$submatchs,PREG_OFFSET_CAPTURE)){
					$l = count($submatchs[0])-1;
					$startPos = $submatchs[0][$l][1];
					$tag = $submatchs[1][$l][0];
					if(preg_match('/<\/'.$tag.'>/',substr($content,$pos+strlen($matches[0][0])),$submatch,PREG_OFFSET_CAPTURE)){
						$endPos = $pos + strlen($matches[0][0]) + strlen($submatch[0][0]) + $submatch[0][1];
						$substr = substr($content,$startPos,$endPos-$startPos);
						$newSubstr = preg_replace('/href="([^"\']*)"/','href="'.$opt['link'].'"',$substr);
						$content = str_replace($substr,$newSubstr,$content);
					}
				}
			}
		}
		$content = preg_replace('/="\/?(?:http:\/\/[^"\']*\/)?(?:img|images)\/([^"\']*)"/','="<?php echo \$html->url(\'/img/newsletter/'.$newsletterFileName.'/$1\',true); ?>"',$content);
		$content = preg_replace('/href="(?!<\?)([^"\']*)"/','href="<?php echo $this->NewsletterMaker->url(\'$1\'); ?>"',$content);

		return $content;
	}
	
	function zipExtractToFlat($zip, $dest, $entries = null){
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