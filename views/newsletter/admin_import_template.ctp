<?php if( $available ) { ?>
	<div class="newsletters form">
	<?php echo $form->create('Newsletter',array('url'=>array('plugin' => 'newsletter', 'controller' => 'newsletter', 'action' => 'import_template'),'type' => 'file'));?>
		<fieldset>
			<legend><?php __('Import Newsletter template');?></legend>
		<?php
			echo $form->input('title');
			echo $form->input('zip_file',array('type'=>'file'));
		?>
		</fieldset>
	<?php echo $form->end(__('Submit', true));?>
	</div>
	<div class="actions">
		<ul>
			<li><?php echo $html->link(__d('newsletter','List Newsletters', true), array('action' => 'index'));?></li>
		</ul>
	</div>
<?php }else{ ?>
	<h2><?php __('This site is not correctly configured to import Newsletter templates.'); ?></h2>
<?php } ?>