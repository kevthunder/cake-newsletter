<div class="newsletterEmails form">
<?php 
$url = null;
if(isset($cur_list_id)){
	$url = array('list_id'=>$cur_list_id);
}
echo $form->create('NewsletterEmail', array('url'=>$url));?>
	<fieldset>
 		<legend><?php __d('newsletter','Edit NewsletterEmail');?></legend>
	<?php
		echo $form->input('id');
		echo $form->input('active');
		echo $form->input('name');
		echo $form->input('email');
	?>
	</fieldset>
<?php echo $form->end(__d('newsletter','Submit',true));?>
</div>
<div class="actions">
	<ul>
		<li><?php echo $html->link(__d('newsletter','Delete', true), array('action' => 'delete', $form->value('NewsletterEmail.id')), null, sprintf(__d('newsletter','Are you sure you want to delete # %s?', true), $form->value('NewsletterEmail.id'))); ?></li>
		<li><?php echo $html->link(__d('newsletter','List NewsletterEmails', true), array('action' => 'index'));?></li>
	</ul>
</div>
