<?php
$script = 'var newsletter_id = '.$this->data["Newsletter"]["id"].';';
$javascript->codeBlock($script,array('inline'=>false));

$script = '
	var root = "'.$html->url('/').'";
	var tinymce_url = "'.$html->url('/js/tiny_mce/tiny_mce.js').'";
';
$javascript->codeBlock($script,array('inline'=>false));

$javascript->link('/newsletter/js/jquery-1.4.2.min.js', false);
$javascript->link('/newsletter/js/jquery-ui-1.7.2.custom.min', false);
$javascript->link('/newsletter/js/jquery.form.js', false);
//$javascript->link('tiny_mce/jquery.tinymce', false);
$javascript->link('/newsletter/js/newsletter', false);

if(isset($multimedia)){
	$multimedia->display('NewsletterBox.empty');
}
?>

<?php $html->css('/newsletter/css/newsletter.admin',null,array('inline'=>false)); ?>

<?php $html->css('/newsletter/css/colorbox.css',null,array('inline'=>false)); ?>
<?php $javascript->link('/newsletter/js/jquery.colorbox-min', false) ?>
<div style="display:none" class="ajax_loader"></div>
<div class="newsletters form">
<?php echo $form->create('Newsletter',array('onsubmit'=>'return newsletter_submit();','url'=>array('plugin' => 'newsletter', 'controller' => 'newsletter', 'action' => 'edit')));?>
	<fieldset>
 		<legend><?php __d('newsletter','Edit Newsletter');?></legend>
	<?php
		echo $form->input('id');
		echo $form->input('active');
		echo $form->input('title',array('label'=>__d('newsletter','Title',true)));
		echo $form->input('date');
		echo $form->input('template',array('options' =>$templates));
		/*echo $form->input('text');*/
	?>
        <fieldset style="background-color:#EEEEEE; width:98%">
            <legend><?php __d('newsletter','Content');?></legend>
            <div id='edit_zone'>
            	<div class='tools'>
                   <?php  echo $form->input('elements',array('options' =>$box_elements,'div'=>array('id'=>'add_elem_box','class'=>'popup'),'before'=>'<a class="close_link">x</a>','after'=>'<a class="add_link">Ajouter</a>')); ?>
                   <div id="edit_form_zone">
				   
                   </div>
                </div>
				<?php 
				if(in_array($this->data['Newsletter']['template'],array_keys($templates))){ ?>
                <div class='preview'>
					<?php echo str_replace(array('<html>','</html>','<body>','</body>','%sended_id%'),'',$this->requestAction(array('plugin'=>'newsletter','controller'=>'newsletter','action'=>'preview','admin'=>true,'prefix'=>'admin'), array('admin'=>true,'pass' => array($this->data['Newsletter']['id'])))); ?>
                </div>
				<?php }else{ ?>
					<h2><?php __('Template file could not be found'); ?></h2>
				<?php } ?>
                
                <br style="clear:both" />
            </div>
        </fieldset>
	</fieldset>
<?php echo $form->end(__d('newsletter','Submit',true));?>
</div>
<div class="actions">
	<ul>
		<li><?php echo $html->link(__d('newsletter','Delete', true), array('action' => 'delete', $form->value('Newsletter.id')), null, sprintf(__d('newsletter','Are you sure you want to delete # %s?', true), $form->value('Newsletter.id'))); ?></li>
		<li><?php echo $html->link(__d('newsletter','List Newsletters', true), array('action' => 'index'));?></li>
	</ul>
</div>
