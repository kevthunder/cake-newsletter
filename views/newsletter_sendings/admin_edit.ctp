<?php 
$html->css('/newsletter/css/newsletter.admin',null,array('inline'=>false)); 
$this->Html->scriptBlock('
	(function( $ ) {
		$(function(){
			$("#NewsletterSendingScheduled").click(testShedule);
			$("#NewsletterSendingScheduled").change(testShedule);
			testShedule();
		})
		function testShedule(){
			if($("#NewsletterSendingScheduled").is(":checked")){
				$(".schedule .datetime select").removeAttr("disabled");
			}else{
				$(".schedule .datetime select").attr("disabled","disabled");
			}
		}
	})( jQuery );
',array('inline'=>false));
?>
<div class="NewsletterSending addForm">
<?php echo $form->create('NewsletterSending');?>
	<fieldset>
 		<legend><?php echo str_replace('%title%',$sending['Newsletter']['title'],__d('newsletter','Send the "%title%" Newsletter',true)); ?></legend>
		<fieldset class="listList">
			<legend><?php __d('newsletter','NewsletterSendlists');?></legend>
			<?php
			echo $form->input('id');
			
			$i = 0;
			$cols = 3;
			$byCol = ceil(count($sendlists)/$cols);
			$listChunks = array_chunk($sendlists,$byCol,true);
			//debug($this->data['NewsletterSending']['selected_lists']);
			foreach($listChunks as $sendlists){
				echo '<div class="col">';
				foreach($sendlists as $key => $label){
					echo $form->input('NewsletterSending.selected_lists.'.$i, array('label'=>$label,'type' => 'checkbox','value'=>$key,'hiddenField' => false, 'checked'=>in_array($key,$this->data['NewsletterSending']['selected_lists'])));
					$i++;
				}
				echo '</div>';
			}
			?>
			</fieldset>
		<?php
		if(!empty($this->O2form)){
			echo $this->O2form->input('additional_emails', array('label'=>__d('newsletter','Additional Emails',true),'type' => 'multiple','fields'=>array('name','email'),'minRows'=>1));
		}else{
			echo $form->input('additional_emails', array('label'=>__d('newsletter','Additional Emails',true),'class'=>'noTinyMce','type' => 'textarea','after'=>'<div class="help">'.__d('newsletter','Separate multiple emails by commas(,). eg: email1@server.com, email2@server.com',true).'</div>'));
		}
		echo $form->input('check_sended', array('label'=>__d('newsletter','Ignore contacts to whom this newsletter has already been sent',true),'type' => 'checkbox', 'checked' => $this->data['NewsletterSending']['check_sended']));
		
		echo $form->input('date', array('label'=>false));
	?>
	</fieldset>
	<?php echo $form->submit(__d('newsletter','Submit',true));?>
<?php echo $form->end();?>
</div>
<div class="actions">
	<ul>
		<li><?php echo $html->link(__('List Newsletters', true), array('plugin'=>'newsletter', 'controller'=>'newsletter', 'action' => 'index'));?></li>
	</ul>
</div>
