<div class="newsletterEmails form">
<?php echo $form->create('NewsletterEmail');?>
	<fieldset>
 		<legend><?php __('Edit NewsletterEmail');?></legend>
	<?php
		echo $form->input('id');
		echo $form->input('active');
		echo $form->input('name');
		echo $form->input('email');
	?>
	</fieldset>
<?php echo $form->end('Submit');?>
</div>
<div class="actions">
	<ul>
		<li><?php echo $html->link(__('Delete', true), array('action' => 'delete', $form->value('NewsletterEmail.id')), null, sprintf(__('Are you sure you want to delete # %s?', true), $form->value('NewsletterEmail.id'))); ?></li>
		<li><?php echo $html->link(__('List NewsletterEmails', true), array('action' => 'index'));?></li>
	</ul>
</div>
