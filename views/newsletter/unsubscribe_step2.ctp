<?php $session->flash(); ?>
<?php echo $form->create('NewsletterEmail',array('name'=>'form','url'=>array('plugin' => 'newsletter', 'controller' => 'newsletter', 'action' => 'unsubscribe', 'id'=>null)));?>
	<p><?php __d('newsletter','Your email')?> : <?php echo $this->data['NewsletterEmail']['email']; ?></p>
    <p><?php __d('newsletter','Are you sure you want to unsubscribe from our newletter ?')?></p>
	<?php
		echo $form->hidden('email');
		echo $form->input('confirm',array('type'=>'checkbox','style'=>'display:none;','div'=>false,'label'=>false));
		echo $form->button(__d('newsletter','Yes',true),array('onclick'=>"document.getElementById('NewsletterEmailConfirm').checked='checked';document.form.submit()"));
		echo $form->button(__d('newsletter','No',true),array('onclick'=>"document.form.submit()"));
	?>
<?php echo $form->end();?>
