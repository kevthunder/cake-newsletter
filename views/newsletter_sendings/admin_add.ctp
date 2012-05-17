<div class="newsletters form">
<?php echo $form->create('NewsletterSending');?>
	<fieldset>
 		<legend><?php __('Send Newsletter');?></legend>
	<?php
		echo $form->input('newsletter_id',array('type'=>'hidden'));
		echo $form->input('selected_lists', array('label'=>__('Listes de diffusion',true),'type' => 'select', 'options' => $sendlists, 'multiple' => true));
		echo $form->input('additional_emails', array('label'=>'Emails supplementaire','class'=>'noTinyMce','type' => 'textarea','after'=>__('Séparez plusieurs emails par des virgules(,); ex: email1@server.com, email2@server.com',true)));
		echo $form->input('check_sended', array('label'=>__('Ignorer les contacts à qui cette infolettre a déjà été envoyé',true),'type' => 'checkbox', 'checked' => true));
	?>
	</fieldset>
<?php echo $form->end('Continue');?>
</div>
<div class="actions">
	<ul>
		<li><?php echo $html->link(__('List Newsletters', true), array('plugin'=>'newsletter', 'controller'=>'newsletter', 'action' => 'index'));?></li>
	</ul>
</div>
