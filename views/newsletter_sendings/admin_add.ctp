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
 		<legend><?php echo str_replace('%title%',$newsletter['Newsletter']['title'],__d('newsletter','Send the "%title%" Newsletter',true)); ?></legend>
	<?php
		echo $form->input('newsletter_id',array('type'=>'hidden'));
		?>
		<fieldset class="listList">
			<legend><?php __d('newsletter','NewsletterSendlists');?></legend>
			<?php
			$i = 0;
			$cols = 3;
			$byCol = ceil(count($sendlists)/$cols);
			$listChunks = array_chunk($sendlists,$byCol,true);
			foreach($listChunks as $sendlists){
				echo '<div class="col">';
				foreach($sendlists as $key => $label){
					echo $form->input('NewsletterSending.selected_lists.'.$i, array('label'=>$label,'type' => 'checkbox','value'=>$key,'hiddenField' => false));
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
			echo $form->input('additional_emails', array('label'=>__d('newsletter','Additional Emails',true).' ('.__d('newsletter','Separate by commas',true).')','class'=>'noTinyMce','type' => 'textarea','after'=>'<div class="help">'.__d('newsletter','Eg: email1@server.com, email2@server.com',true).'</div>'));
		}
		echo $form->input('check_sended', array('label'=>__d('newsletter','Ignore contacts to whom this newsletter has already been sent',true),'type' => 'checkbox', 'checked' => true));
		
		
		if( NewsletterConfig::load('cron') ) {
	?>
	<div class="schedule">
	<?php 
		echo $form->input('scheduled', array('label'=>__d('newsletter','Send automatically at :',true),'type' => 'checkbox'));
		echo $form->input('date', array('label'=>false));
	?>
	</div>
	<?php }?>
	</fieldset>
	<?php echo $form->submit(__d('newsletter','Continue',true));?>
	<?php if( !empty($newsletter['Newsletter']['tested'] ) ) { ?>
		<span class="validated"><?php __d('newsletter','This newsletter has been tested'); ?></span>
	<?php }?>
<?php echo $form->end();?>
</div>
<div class="actions">
	<ul>
		<li><?php echo $html->link(__d('newsletter','Back to Newsletters List', true), array('plugin'=>'newsletter', 'controller'=>'newsletter', 'action' => 'index'));?></li>
	</ul>
</div>
