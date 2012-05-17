<?php
	$this->Html->scriptBlock('
		(function( $ ) {
			$(function(){
				$.ajax({
				  url: "'.$this->Html->url(array('action'=>'resume','stream'=>1,$sending['NewsletterSending']['id'])).'",
				}).done(function ( data ) {
				  if( console && console.log ) {
					console.log(data);
				  }
				  $(".SendingDone").show();
				  $(".SendingWait").hide();
				});
			})
		})( jQuery );
	',array('inline'=>false));
?>

<div class="SendingWait">
	<h1><?php __('The newsletter is being sent'); ?></h1>
	<div class="loading"><img src="<?php echo $this->Html->url('/newsletter/img/ajax-loader-big.gif'); ?>" alt="" /></div>
</div>
<div class="SendingDone" style="display:none;">
	<h2><?php __('The newsletter has been successfully sent'); ?></h2>
</div>