

<div class="newsletterSendlists form">
<?php echo $form->create('NewsletterSendlist',array('type'=>'file'));?>
	<fieldset>
 		<legend><?php __d('newsletter','Import NewsletterSendlist');?></legend>
		<?php echo $this->Form->input('import_file',array('label'=>__d('newsletter','File to import',true),'type' => 'file')); ?>
        <?php echo $this->Form->input('import_path',array('type' => 'hidden')); ?>
	</fieldset>
<?php echo $form->end(__('Submit',true));?>
</div>