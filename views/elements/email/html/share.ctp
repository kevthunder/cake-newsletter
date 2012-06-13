<?php 
$replace = array(
	'%sender_name%'=>h($sending['NewsletterSending']['sender_name']),
	'%break%'=>'</p>'."\n".'<p>',
	'%br%'=>'<br />',
	'%subscribeLink%' => '<a href="'.$this->Html->url(array('plugin'=>'newsletter', 'controller'=>'newsletter_emails','action'=>'add','admin'=>false),true).'">',
	'%subscribeLinkEnd%' => '</a>',
);
if( empty($sending['NewsletterSending']['data']['msg']) ) {
	$p1 = __('Bonjour,%br%%sender_name% croit que cette infolettre pourrait vous intéresser.',true); 
}else{
	$replace['%msg%'] = nl2br(h($sending['NewsletterSending']['data']['msg']));
	$p1 = __('Bonjour,%br%%sender_name% croit que cette infolettre pourrait vous intéresser et vous envoie ce message :%break%%msg%',true); 
}

$p2 = __('Veuillez prendre note que votre adresse courriel n\'a pas été ajoutée notre liste de diffusion. Si vous n’y êtes pas abonné et que vous désirez vous inscrire gratuitement, %subscribeLink%cliquez ici%subscribeLinkEnd%. Nous ne partagerons jamais votre adresse électronique.',true); 

//debug($sending);

?>

<table border="0" width="100%" cellpadding="10">
	<tr>
		<td></td>
		<td width="500" bgcolor="#FFFFFF">
<p><?php echo str_replace(array_keys($replace),array_values($replace),$p1) ?></p> 
<p><?php echo str_replace(array_keys($replace),array_values($replace),$p2) ?></p></td>
		<td></td>
	</tr>
</table>

<br />
<?php echo $newsletterContent ?>