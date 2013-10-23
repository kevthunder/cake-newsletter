<?php echo $session->flash(); ?>
<div class="newsletterEmails form">
<?php echo $form->create('NewsletterEmail');?>
	<fieldset>
 		<legend><?php __d('newsletter','Subscribe to our Newletter');?></legend>
	<?php
		echo $form->input('name');
		echo $form->input('email');
		if(!empty($sendlists) && count($sendlists) > 1){
			echo $form->input('sendlist_id', array('label'=>__d('newsletter','Sendlist',true), 'options'=>$sendlists, 'multiple'=>'checkbox'));
		}
	?>
	</fieldset>
<?php echo $form->end(__d('newsletter','Subscribe',true));?>
</div>