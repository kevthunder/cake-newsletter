

<div class="newsletterSendlists form">
<?php echo $form->create('NewsletterSendlist',array('type'=>'file'));?>
	<fieldset>
 		<legend><?php __('Import NewsletterSendlist');?></legend>
		<?php echo $this->Form->input('import_file',array('label'=>__('File to import',true),'type' => 'file')); ?>
        <?php echo $this->Form->input('import_path',array('type' => 'hidden')); ?>
	</fieldset>
<?php echo $form->end('Submit');?>
</div>