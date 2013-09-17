<h1><?php 
if(isset($newsletter_box["NewsletterBox"]["data"])){
	echo $newsletter_box["NewsletterBox"]["data"]["title"]; 
}else{
	__d('newsletter','Title');
}
?></h1>
<?php 
if(isset($newsletter_box["NewsletterBox"]["data"])){
	echo $this->NewsletterMaker->filterRichtext($newsletter_box["NewsletterBox"]["data"]["text"]); 
}else{
	echo 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut vitae orci eu erat imperdiet interdum.';
}
?>
<?php if(!empty($newsletter_box["NewsletterBox"]["data"]["url"])){ ?>
	<br /><a href="<?php echo $newsletterMaker->url($newsletter_box["NewsletterBox"]["data"]["url"]); ?>"><?php echo $newsletter_box["NewsletterBox"]["data"]["url_text"]; ?></a>
<?php } ?>
&nbsp;
