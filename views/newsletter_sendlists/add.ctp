<div class="newsletterSendlists form">
<?php echo $form->create('NewsletterSendlist');?>
	<fieldset>
 		<legend><?php __('Add NewsletterSendlist');?></legend>
	<?php
		echo $form->input('active');
		echo $form->input('title');
		echo $form->input('description');
	?>
	</fieldset>
<?php echo $form->end('Submit');?>
</div>
<div class="actions">
	<ul>
		<li><?php echo $html->link(__('List NewsletterSendlists', true), array('action' => 'index'));?></li>
	</ul>
</div>
