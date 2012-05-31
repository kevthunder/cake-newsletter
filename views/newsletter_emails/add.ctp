<?php echo $session->flash(); ?>
<div class="newsletterEmails form">
<?php echo $form->create('NewsletterEmail');?>
	<fieldset>
 		<legend><?php __d('newsletter','Subscribe to our Newletter');?></legend>
	<?php
		echo $form->input('name');
		echo $form->input('email');
	?>
	</fieldset>
<?php echo $form->end(__d('newsletter','Subscribe',true));?>
</div>