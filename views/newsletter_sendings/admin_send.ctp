<?php $html->css('/newsletter/css/newsletter.admin',null,array('inline'=>false)); ?>
<?php $javascript->link('/newsletter/js/send_console', false); ?>
<div class="NewsletterSending form">
	<fieldset>
 		<legend><?php __('Send your Newsletter');?></legend>
			<div class="consoleContainer" stream="<?php echo $html->url(array('action'=>'status',$newsletterSending['NewsletterSending']['id'])); ?>">
				<div class="console_box">
					<div class="console">
					</div>
				</div>
				<div class="actions">
					<a href="<?php echo $html->url(array('action'=>'start',$newsletterSending['NewsletterSending']['id'])); ?>" class="button ajax_button highlight_button tstart_bt" confirm="<?php __('Voulez-vous vraiment envoyer la Newsletter maintenant?') ?>">Start</a>
					<a href="<?php echo $html->url(array('action'=>'pause',$newsletterSending['NewsletterSending']['id'])); ?>" class="button ajax_button pause_bt">Pause</a>
					<a href="<?php echo $html->url(array('action'=>'cancel',$newsletterSending['NewsletterSending']['id'])); ?>" class="button cancel_bt">Cancel</a>
				</div>
			</div>
		</div>
	</fieldset>
</div>
<div class="actions">
	<ul>
		<li><?php echo $html->link(__('List Newsletters', true), array('plugin'=>'newsletter', 'controller'=>'newsletter', 'action' => 'index'));?></li>
	</ul>
</div>
