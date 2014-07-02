<?php 
if(isset($newsletter_box["NewsletterBox"]["file"]["photo"])){
	$size = empty($newsletter_box["NewsletterBox"]["data"]["size"]) ? '700x700' : $newsletter_box["NewsletterBox"]["data"]["size"];
	
	$imgSrc = $this->Photo->path($newsletter_box["NewsletterBox"]["file"]["photo"]["path"],$newsletter_box["NewsletterBox"]["file"]["photo"]["file"],array(
		'method'=>'resize',
		'size'=>$size,
		'full'=>true
	));
}elseif(!empty($newsletter_box["NewsletterBox"]["data"]["default_photo"])){
	$imgSrc = $html->url($newsletter_box["NewsletterBox"]["data"]["default_photo"],true);
}
if(!empty($imgSrc)){
	$img = '<img src="'.$imgSrc.'" />';
	if(!empty($newsletter_box["NewsletterBox"]["data"]["url"])){
		echo '<a href="'.$newsletterMaker->url($newsletter_box["NewsletterBox"]["data"]["url"]).'">'.$img.'</a>';
	}else{
		echo $img;
	}
} else {
	echo "Zone image";
}
?>