<?php 
if(Configure::read('Newsletter.ConfirmEmail.text')){
	$msg = Configure::read('Newsletter.ConfirmEmail.text');
}else{
	$msg = __('Hello %name%,
Thank you for signing up to our Neswletter.',true);
}

$search  = array('%name%');
$replace = array($newsletterEmail['NewsletterEmail']['name']);
$result = str_replace($search, $replace, $msg);

echo $result;
?>