<?php
	class UpTemplateShell extends Shell {
		function main() {
			App::import('Lib', 'Newsletter.NewsletterConfig');
			$templates = NewsletterConfig::getTemplatesConfig();
			$nbTemplates = array_values($templates);
			$template = null;
			if(!empty($this->args[0]) && !empty($templates[$this->args[0]])){
				$template = $templates[$this->args[0]];
			}
			if(!empty($this->args[0]) && file_exists(getcwd().DS.$this->args[0])){
				$this->updateTemplateFile(getcwd().DS.$this->args[0]);
				return;
			}
			while(!$template){
				$this->out();
				$this->templateList($templates);
				$this->out();
				$res = $this->in(__('Which template ?',true).' ('.__('Enter a number from the list above or "q" to quit',true).')',null,'q');
				
				if($res == 'q') exit();
				
				if(!empty($nbTemplates[$res])){
					$template = $nbTemplates[$res];
				}
			}
			$this->updateTemplate($template);
		}
		
		function templateList($templates){
			foreach(array_values($templates) as $i => $template){
				$this->out($i.'. '.$template->getLabel());
			}
		}
		
		function updateTemplate($template){
			$file = $template->getPath();
			$this->updateTemplateFile($file);
		}
		function updateTemplateFile($file){
			$this->out(str_replace('%file%',$file,__('Editing %file%...',true)));
			$contents = file_get_contents($file);
			$contents = str_replace('$newsletter->','$this->NewsletterMaker->',$contents);
			file_put_contents($file,$contents);
			$this->out(__('Done',true));
			
		}
	}
?>