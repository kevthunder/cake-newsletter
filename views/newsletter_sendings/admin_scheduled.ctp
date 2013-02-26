<?php $html->css('/newsletter/css/newsletter.admin',null,array('inline'=>false)); ?>
<div class="NewsletterSending sendForm">
	<h2><?php 
		echo str_replace(
				array('%title%','%date%'),
				array($newsletterSending['Newsletter']['title'],date_('jS F Y G\hi',strtotime($newsletterSending['NewsletterSending']['date']))),
				__d('newsletter','The "%title%" Newsletter is scheduled to be sent around the %date%',true)
			); 
	?></h2>
	<p><?php __d('newsletter','This time can vary depending on your server configuration and performance.'); ?></p>
</div>
<div class="actions">
	<ul>
		<li><?php echo $html->link(__('List Newsletters', true), array('plugin'=>'newsletter', 'controller'=>'newsletter', 'action' => 'index'));?></li>
	</ul>
</div>
