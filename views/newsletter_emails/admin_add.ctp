<div class="newsletterEmails form">
<?php 
$url = null;
if(isset($cur_list_id)){
	$url = array('list_id'=>$cur_list_id);
}
echo $form->create('NewsletterEmail', array('url'=>$url));?>
	<fieldset>
 		<legend><?php __d('newsletter','Add NewsletterEmail');?></legend>
	<?php
		echo $form->input('active', array('checked'=>1));
		echo $form->input('name',array('label'=>__d('newsletter','Name',true)));
		echo $form->input('email');
		echo $form->input('sendlist_id',array('label'=>__d('newsletter','Sendlist',true)));
	?>
	</fieldset>
<?php echo $form->end(__d('newsletter','Submit',true));?>
</div>
<div class="actions">
	<ul>
        <li><?php echo $html->link(__d('newsletter','Back to Sendlists', true), array('plugin'=>'newsletter','controller' => 'newsletter_sendlists', 'action' => 'index')); ?></li>
	</ul>
</div>
