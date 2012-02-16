<div class="newsletters form">
<?php echo $form->create('Newsletter');?>
	<fieldset>
 		<legend><?php __('Add Newsletter');?></legend>
	<?php
		echo $form->input('active',array('default'=>1));
		echo $form->input('title');
		echo $form->input('date');
		echo $form->input('template',array('options' =>$templates));
	?>
	</fieldset>
<?php echo $form->end('Continue');?>
</div>
<div class="actions">
	<ul>
		<li><?php echo $html->link(__('List Newsletters', true), array('action' => 'index'));?></li>
	</ul>
</div>
