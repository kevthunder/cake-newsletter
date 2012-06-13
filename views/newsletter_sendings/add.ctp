<div class="newsletters form">
<?php echo $form->create('NewsletterSending',array('onsubmit'=>'return confirm("'.__('Voulez-vous vraiment envoyer la Newsletter maintenant?',true).'");'));?>
	<fieldset>
 		<legend><?php echo str_replace('%title%',$newsletter['Newsletter']['title'],__('Send Newsletter "%title%" to a friend',true));?></legend>
	<?php
		echo $form->input('newsletter_id',array('type'=>'hidden'));
		echo $form->input('sender_name', array('label'=>__('Your name',true)));
		echo $form->input('sender_email', array('label'=>__('Your email',true)));
		if(!empty($this->O2form)){
			echo $this->O2form->input('additional_emails', array('label'=>__('Recipients',true),'type' => 'multiple','fields'=>array('name','email'),'minRows'=>1));
		}else{
			echo $form->input('additional_emails', array('label'=>__('Friend email',true),'class'=>'noTinyMce','type' => 'textarea','after'=>__('Séparez plusieurs emails par des virgules(,); ex: email1@server.com, email2@server.com',true)));
		}
		echo $form->input('NewsletterSending.data.msg', array('label'=>__('Personalized message',true).' ('.__('optional',true).')','type'=>'textarea'));
	?>
	</fieldset>
<?php echo $form->end(__('Send',true));?>
</div>
