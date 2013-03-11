<?php $html->css('/newsletter/css/newsletter.admin',null,array('inline'=>false)); ?>
<div class="NewsletterSending sendForm">
	<h2><?php 
		echo str_replace(
				array('%title%','%date%'),
				array($newsletterSending['Newsletter']['title'],date_('jS F Y G\hi',strtotime($newsletterSending['NewsletterSending']['date']))),
				__d('newsletter','The "%title%" Newsletter will be sent the %date%',true)
			); 
	?></h2>
	<p><?php __d('newsletter','You can schedule some more sendings by repeating the last steps.'); ?></p>
</div>
<div class="actions">
	<ul>
		<li><?php echo $html->link(__d('newsletter','Back to Newsletters List', true), array('plugin'=>'newsletter', 'controller'=>'newsletter', 'action' => 'index'));?></li>
	</ul>
</div>
