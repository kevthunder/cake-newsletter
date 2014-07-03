<?php 
$link_yes = isset($newsletter_box["NewsletterBox"]["data"]["link_yes"]) ? $newsletter_box["NewsletterBox"]["data"]["link_yes"] : 'yes';
$link_no = isset($newsletter_box["NewsletterBox"]["data"]["link_no"]) ? $newsletter_box["NewsletterBox"]["data"]["link_no"] : 'no';

?>
	<a href="<?php echo $this->NewsletterMaker->reenableUrl(); ?>" class=""><?php echo $link_yes ?></a>
	<br/>
	<a href="<?php echo $this->NewsletterMaker->declineUrl(); ?>" class=""><?php echo $link_no ?></a>
