<?php
	$title = isset($newsletter_box["NewsletterBox"]["data"]['title']) ? $newsletter_box["NewsletterBox"]["data"]['title'] : __d('newsletter','Title',true);
	$text = isset($newsletter_box["NewsletterBox"]["data"]['text']) ? $newsletter_box["NewsletterBox"]["data"]['text'] : 'Lorem ipsum dolor sit amèt, consectetur adipiscing elit. Ut vitae orci eu erat imperdiet interdum.';
	$text = $this->NewsletterMaker->filterRichtext($text);
	$link = isset($newsletter_box["NewsletterBox"]["data"]['url']) ? $newsletter_box["NewsletterBox"]["data"]['url'] : null;
	$link_text = isset($newsletter_box["NewsletterBox"]["data"]['url_text']) ? $newsletter_box["NewsletterBox"]["data"]['url_text'] : 'Lire la suite';
	if(isset($newsletter_box["NewsletterBox"]["file"]["photo"])){
		$img = $this->Photo->path($newsletter_box["NewsletterBox"]["file"]["photo"]["path"],$newsletter_box["NewsletterBox"]["file"]["photo"]["file"],array('method'=>'resize','size'=>'700x700','full'=>true));
	}
?>

<h1><?php echo $title ?></h1>
<?php if(!empty($img)){ ?>
	<img src="<?php echo $img ?>" /><br />
    <br />
<?php } ?>
<?php echo $text ?>
<?php if(!empty($link)){ ?>
	<br /><a href="<?php echo $newsletterMaker->url($link); ?>"><?php echo $link_text; ?></a>
<?php } ?>
&nbsp;
