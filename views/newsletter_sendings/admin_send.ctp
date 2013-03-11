<?php $html->css('/newsletter/css/newsletter.admin',null,array('inline'=>false)); ?>
<?php $javascript->link('/newsletter/js/send_console', false); ?>
<div class="NewsletterSending sendForm">
	<fieldset>
 		<legend><?php echo str_replace('%title%',$newsletterSending['Newsletter']['title'],__d('newsletter','Send the "%title%" Newsletter',true)); ?></legend>
		<div class="note"><?php echo '('.__d('newsletter','You need to press "Start"',true).')';?></div>
			<div class="consoleContainer" stream="<?php echo $html->url(array('action'=>'status',$newsletterSending['NewsletterSending']['id'])); ?>">
				<div class="console_box">
					<div class="console">
					<?php if(!empty($newsletterSending['NewsletterSending']['console'])) { ?>
						<div class="original_log">
							<div class="console_content">
							<?php 
								echo nl2br($newsletterSending['NewsletterSending']['console']);
							 ?>
							</div>
							<ul class="stats">
							<?php foreach($statistics as $label => $statistic){ ?>
								<li>
									<span class="label"><?php __($label); ?> :<span>
									<span class="label"><?php echo $statistic; ?><span>
								</li>
							<?php } ?>
							</ul>
						</div>
					<?php }?>
					</div>
				</div>
				<div class="actions">
					<a href="<?php echo $html->url(array('action'=>'start',$newsletterSending['NewsletterSending']['id'])); ?>" class="button ajax_button highlight_button tstart_bt" confirm="<?php __d('newsletter','Do you really want to send the newsletter now?') ?>"><?php __d('newsletter','Start'); ?></a>
					<a href="<?php echo $html->url(array('action'=>'pause',$newsletterSending['NewsletterSending']['id'])); ?>" class="button ajax_button pause_bt"><?php __d('newsletter','Pause'); ?></a>
					<a href="<?php echo $html->url(array('action'=>'cancel',$newsletterSending['NewsletterSending']['id'])); ?>" class="button cancel_bt"><?php __d('newsletter','Cancel'); ?></a>
				</div>
			</div>
		</div>
	</fieldset>
</div>
<div class="actions">
	<ul>
		<li><?php echo $html->link(__d('newsletter','Back to Newsletters List', true), array('plugin'=>'newsletter', 'controller'=>'newsletter', 'action' => 'index'));?></li>
	</ul>
</div>
