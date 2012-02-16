<?php 
if(Configure::read('Newsletter.ConfirmEmail.htmlText')){
	$msg = Configure::read('Newsletter.ConfirmEmail.htmlText');
}elseif(Configure::read('Newsletter.ConfirmEmail.text')){
	$msg = nl2br(Configure::read('Newsletter.ConfirmEmail.text'));
}else{
	$msg = __('Hello %name%,<br>
Thank you for signing up to our Neswletter.',true);
}

$search  = array('%name%');
$replace = array($newsletterEmail['NewsletterEmail']['name']);
$result = str_replace($search, $replace, $msg);

echo $result;
?>