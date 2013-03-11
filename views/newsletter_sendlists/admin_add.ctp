<div class="newsletterSendlists form">
<?php echo $form->create('NewsletterSendlist');?>
	<fieldset>
 		<legend><?php __d('newsletter','Add NewsletterSendlist');?></legend>
	<?php
		echo $form->input('active', array('checked'=>1));
		echo $form->input('title');
		echo $form->input('description');
	?>
	</fieldset>
<?php echo $form->end(__d('newsletter','Submit',true));?>
</div>
<div class="actions">
	<ul>
		<li><?php echo $html->link(__d('newsletter','Back to Sendlists', true), array('action' => 'index'));?></li>
	</ul>
</div>
