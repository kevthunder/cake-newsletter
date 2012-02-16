<div class="newsletterSendingOutput" <?php if($ajax) echo ' json="'.h(json_encode($json)).'"';?>>
	<div class="console_content">
	<?php 
		echo nl2br($sending['NewsletterSending']['console']);
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
<div>