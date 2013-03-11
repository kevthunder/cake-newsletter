<div class="newsletters form">
<?php echo $form->create('Newsletter',array('url'=>array('plugin' => 'newsletter', 'controller' => 'newsletter', 'action' => 'add')));?>
	<fieldset>
 		<legend><?php __d('newsletter','Add Newsletter');?></legend>
	<?php
		echo $form->input('active',array('type'=>'hidden','value'=>0));
		echo $form->input('title',array('label'=>__d('newsletter','Title',true)));
		echo $form->input('date');
		echo $form->input('template',array('options' =>$templates,'label'=>__d('newsletter','Template',true)));
	?>
	</fieldset>
<?php echo $form->end(__d('newsletter','Continue', true));?>
</div>
<div class="actions">
	<ul>
		<li><?php echo $html->link(__d('newsletter','Back to Newsletters List', true), array('action' => 'index'));?></li>
	</ul>
</div>
