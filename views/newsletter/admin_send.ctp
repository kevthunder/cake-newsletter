<div class="newsletters form">
<?php echo $form->create('Newsletter',array('onsubmit'=>'return confirm("'.__('Voulez-vous vraiment envoyer la Newsletter maintenant?',true).'");','url'=>array('plugin' => 'newsletter', 'controller' => 'newsletter', 'action' => 'send')));?>
	<fieldset>
 		<legend><?php __('Send Newsletter');?></legend>
	<?php
		echo $form->input('sending_no',array('type'=>'hidden'));
		echo $form->input('id');
		echo $form->input('sendlists', array('label'=>__('Listes de diffusion',true),'type' => 'select', 'options' => $sendlists, 'multiple' => true));
		echo $form->input('emails', array('label'=>'Emails supplementaire','class'=>'noTinyMce','type' => 'textarea','after'=>__('Séparez plusieurs emails par des virgules(,); ex: email1@server.com, email2@server.com',true)));
		echo $form->input('check_sended', array('label'=>__('Ignorer les contacts à qui cette infolettre a déjà été envoyé',true),'type' => 'checkbox', 'checked' => true));
	?>
	</fieldset>
<?php echo $form->end('Continue');?>
</div>
<div class="actions">
	<ul>
		<li><?php echo $html->link(__('List Newsletters', true), array('action' => 'index'));?></li>
	</ul>
</div>
