<?php 
if(isset($newsletter_box["NewsletterBox"]["data"])){
	$attr = '';
	if(!empty($newsletter_box["NewsletterBox"]["data"]["attr"])){
		$attr = $this->NewsletterMaker->_parseAttributes($newsletter_box["NewsletterBox"]["data"]["attr"]);
	}
	$link = isset($newsletter_box["NewsletterBox"]["data"]['url']) ? $newsletter_box["NewsletterBox"]["data"]['url'] : '#';
	$title = isset($newsletter_box["NewsletterBox"]["data"]['title']) ? $newsletter_box["NewsletterBox"]["data"]['title'] : __d('newsletter','Title',true);
	if(!empty($newsletter_box["NewsletterBox"]["data"]["wrap"])){
		$title = str_replace('%s',$title,$newsletter_box["NewsletterBox"]["data"]["wrap"]);
	}
	echo '<a href="'.$this->NewsletterMaker->url($link).'"'.$attr.'>';
	echo $title;
	echo '</a>'; 
}else{
	echo '<a href="#">Lorem ipsum</a>';
}
?>
