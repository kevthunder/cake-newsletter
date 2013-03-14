<div class="NewsletterSending form">
	<fieldset>
 		<legend><?php echo str_replace('%title%',$newsletter['Newsletter']['title'],__d('newsletter','Testing the "%title%" Newsletter',true)); ?></legend>
		<?php 
			echo $form->create('NewsletterSending',array('action'=>'admin_test'));
			if(!empty($newsletterSending)){
				echo $form->input('id');
			}else{
				echo $form->hidden('newsletter_id',array('value'=>$newsletter['Newsletter']['id']));
			}
			echo $form->input('test_email',array('label'=>__d('newsletter','Courriel',true)));
			echo $form->end(__d('newsletter','Send',true));
		?>
		</div>
	</fieldset>
</div>
<div class="actions">
	<ul>
		<li><?php echo $html->link(__d('newsletter','Back to Newsletters List', true), array('plugin'=>'newsletter', 'controller'=>'newsletter', 'action' => 'index'));?></li>
	</ul>
</div>
