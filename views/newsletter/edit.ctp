<div class="newsletters form">
<?php echo $form->create('Newsletter');?>
	<fieldset>
 		<legend><?php __('Edit Newsletter');?></legend>
	<?php
		echo $form->input('id');
		echo $form->input('active');
		echo $form->input('title');
		echo $form->input('date');
		echo $form->input('text');
		echo $form->input('template');
		echo $form->input('cache_file');
	?>
	</fieldset>
<?php echo $form->end('Submit');?>
</div>
<div class="actions">
	<ul>
		<li><?php echo $html->link(__('Delete', true), array('action' => 'delete', $form->value('Newsletter.id')), null, sprintf(__('Are you sure you want to delete # %s?', true), $form->value('Newsletter.id'))); ?></li>
		<li><?php echo $html->link(__('List Newsletters', true), array('action' => 'index'));?></li>
	</ul>
</div>
