<?php echo $session->flash(); ?>
<?php echo $form->create('NewsletterEmail',array('url'=>array('plugin' => 'newsletter', 'controller' => 'newsletter', 'action' => 'unsubscribe')));?>
	<?php
		echo $form->input('email');
	?>
<?php echo $form->end(__d('newsletter','Unsubscribe', true));?>

