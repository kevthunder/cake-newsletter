<?php $html->css('/newsletter/css/newsletter.admin',null,array('inline'=>false)); ?>
<?php $javascript->link('/newsletter/js/jquery.form.js', false); ?>
<?php $html->scriptBlock('
	var test_email_sent = false;
	$(function(){
		
		$("#NewsletterSendingAdminTestForm").ajaxForm({
			"beforeSubmit" : function() { 
				$ajax_testing = $("#NewsletterSendingAdminTestForm div.ajax_testing");
				$("div.submit",$ajax_testing).hide();
				$load = $(document.createElement("div"));
				$load.addClass("loading");
				$ajax_testing.append($load);
			},
			"success" : function(res,status) { 
				$ajax_testing = $("#NewsletterSendingAdminTestForm div.ajax_testing");
				$("div.loading",$ajax_testing).remove();
				$msg = $(document.createElement("div"));
				$msg.addClass("message").append(res);
				$ajax_testing.append($msg);
				$("#NewsletterSendingAdminConfirmForm div.submit_normal").show();
				$("#NewsletterSendingAdminConfirmForm div.submit_ignore").hide();
				test_email_sent = true;
			}
		});
	});
	function confirmMsg(){
		if(test_email_sent){
			//return "'.__('Voulez-vous vraiment envoyer la Newsletter ?',true).'";
		}else{
			return "'.__('Voulez-vous vraiment envoyer la Newsletter sans la tester d\'abord? \n\n Il est grandement conseillé de tester votre newsletter avant de l\'envoyé.',true).'";
		}
	}
	function confirmSend(){
		var msg = confirmMsg();
		if(msg){
			return confirm(msg);
		}
		return true;
	}
',array('inline'=>false)); ?>


<div class="NewsletterSending form">
	<fieldset>
 		<legend><?php __('Testing your Newsletter');?></legend>
		<?php 
			echo $form->create('NewsletterSending',array('class'=>'test_form','action'=>'admin_test'));
			echo $form->input('id');
			echo $form->input('test_email',array('div'=>array('class'=>'input text ajax_testing clearfix'),'after'=>$form->submit(__('Send',true))));
			echo $form->end();
		?>
		</div>
		<?php 
			echo $form->create('NewsletterSending',array('onsubmit'=>'return confirmSend();'));
			echo $form->input('id');
			echo $form->input('confirm',array('type'=>'hidden','value'=>1));
			echo $html->link('<< '.__('Edit Newsletter', true), array('plugin'=>'newsletter', 'controller'=>'newsletter', 'action' => 'edit', $newsletterSending['NewsletterSending']['newsletter_id']));
			echo $form->submit(__('Continue Without testing',true),array('div'=>array('class'=>'submit submit_ignore')));
			echo $form->submit(__('The email looks good, Continue',true),array('div'=>array('class'=>'submit submit_normal')));
			echo $form->end();
		?>
	</fieldset>
</div>
<div class="actions">
	<ul>
		<li><?php echo $html->link(__('List Newsletters', true), array('plugin'=>'newsletter', 'controller'=>'newsletter', 'action' => 'index'));?></li>
	</ul>
</div>
