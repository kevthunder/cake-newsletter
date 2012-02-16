<?php 
if(isset($newsletter_box["NewsletterBox"]["file"]["photo"])){
	$img = '<img src="'.$html->url($newsletter_box["NewsletterBox"]["file"]["photo"]["path"].$newsletter_box["NewsletterBox"]["file"]["photo"]["file"],true).'" />';
	if(!empty($newsletter_box["NewsletterBox"]["data"]["url"])){
		echo '<a href="'.$newsletterMaker->url($newsletter_box["NewsletterBox"]["data"]["url"]).'">'.$img.'</a>';
	}else{
		echo $img;
	}
} else {
	echo "Zone image";
}
?>