
<?php 
if(isset($newsletter_box["NewsletterBox"]["data"])){
	echo $this->NewsletterMaker->filterRichtext($newsletter_box["NewsletterBox"]["data"]["text"]); 
}else{
	echo 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut vitae orci eu erat imperdiet interdum.';
}
?>
