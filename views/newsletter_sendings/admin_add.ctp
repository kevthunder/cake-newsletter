<div class="newsletters form">
<?php echo $form->create('NewsletterSending');?>
	<fieldset>
 		<legend><?php __('Send Newsletter');?></legend>
	<?php
		echo $form->input('newsletter_id',array('type'=>'hidden'));
		?>
		<fieldset>
			<legend><?php __('Listes de diffusion');?></legend>
			<?php
			$i = 0;
			foreach($sendlists as $key => $label){
				echo $form->input('NewsletterSending.selected_lists.'.$i, array('label'=>$label,'type' => 'checkbox','value'=>$key,'hiddenField' => false));
				$i++;
			}
			?>
			</fieldset>
		<?php
		if(!empty($this->O2form)){
			echo $this->O2form->input('additional_emails', array('label'=>__('Emails supplementaire',true),'type' => 'multiple','fields'=>array('name','email'),'minRows'=>1));
		}else{
			echo $form->input('additional_emails', array('label'=>__('Emails supplementaire',true),'class'=>'noTinyMce','type' => 'textarea','after'=>__('Séparez plusieurs emails par des virgules(,); ex: email1@server.com, email2@server.com',true)));
		}
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
