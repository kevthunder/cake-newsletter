<div class="newsletterSendlists form">
<?php echo $form->create('NewsletterSendlist');?>
	<fieldset>
 		<legend><?php __('Edit NewsletterSendlist');?></legend>
	<?php
		echo $form->input('id');
		echo $form->input('active');
		echo $form->input('title');
		echo $form->input('description');
	?>
	</fieldset>
<?php echo $form->end('Submit');?>
</div>
<div class="actions">
	<ul>
		<li><?php echo $html->link(__('Delete', true), array('action' => 'delete', $form->value('NewsletterSendlist.id')), null, sprintf(__('Are you sure you want to delete # %s?', true), $form->value('NewsletterSendlist.id'))); ?></li>
		<li><?php echo $html->link(__('List NewsletterSendlists', true), array('action' => 'index'));?></li>
	</ul>
</div>
