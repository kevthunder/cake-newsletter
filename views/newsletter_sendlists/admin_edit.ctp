<div class="newsletterSendlists form">
<?php echo $form->create('NewsletterSendlist');?>
	<fieldset>
 		<legend><?php __d('newsletter','Edit NewsletterSendlist');?></legend>
	<?php
		echo $form->input('id');
		echo $form->input('active');
		if(!$tabled){
			echo $form->input('subscriptable',array('label'=>__d('newsletter','Can be subscripted to',true)));
		}
		echo $form->input('title');
		echo $form->input('description');
	?>
	</fieldset>
<?php echo $form->end(__d('newsletter','Submit',true));?>
</div>
<div class="actions">
	<ul>
		<li><?php echo $html->link(__d('newsletter','Delete', true), array('action' => 'delete', $form->value('NewsletterSendlist.id')), null, sprintf(__('Are you sure you want to delete # %s?', true), $form->value('NewsletterSendlist.id'))); ?></li>
		<li><?php echo $html->link(__d('newsletter','Back to Sendlists', true), array('action' => 'index'));?></li>
	</ul>
</div>
