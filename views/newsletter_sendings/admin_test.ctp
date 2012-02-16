<div class="NewsletterSending form">
	<fieldset>
 		<legend><?php __('Testing your Newsletter');?></legend>
		<?php 
			echo $form->create('NewsletterSending',array('action'=>'admin_test'));
			echo $form->input('id');
			echo $form->input('test_email');
			echo $form->end(__('Send',true));
		?>
		</div>
	</fieldset>
</div>
<div class="actions">
	<ul>
		<li><?php echo $html->link(__('List Newsletters', true), array('plugin'=>'newsletter', 'controller'=>'newsletter', 'action' => 'index'));?></li>
	</ul>
</div>
