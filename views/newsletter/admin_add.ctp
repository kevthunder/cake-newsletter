<div class="newsletters form">
<?php echo $form->create('Newsletter',array('url'=>array('plugin' => 'newsletter', 'controller' => 'newsletter', 'action' => 'add')));?>
	<fieldset>
 		<legend><?php __d('newsletter','Add Newsletter');?></legend>
	<?php
		echo $form->input('active',array('type'=>'hidden','value'=>0));
		echo $form->input('title',array('label'=>__d('newsletter','Title',true)));
		echo $form->input('date');
		echo $form->input('template',array('options' =>$templates,'label'=>__d('newsletter','Template',true)));
		if(!empty($langs)){
			if(count($langs) > 1){
				echo $form->input('lang',array('label'=>__('Language',true),'options'=>$langs,'empty'=>true));
			}else{
				echo $form->input('lang',array('type'=>'hidden','value'=>reset(array_keys($langs))));
			}
		}
	?>
	</fieldset>
<?php echo $form->end(__d('newsletter','Continue', true));?>
</div>
<div class="actions">
	<ul>
		<li><?php echo $html->link(__d('newsletter','Back to Newsletters List', true), array('action' => 'index'));?></li>
	</ul>
</div>
