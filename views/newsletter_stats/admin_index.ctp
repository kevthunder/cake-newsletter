<?php 
$html->css('/newsletter/css/newsletter.admin',null,array('inline'=>false));
$javascript->link('/newsletter/js/swfobject.js', false); 
$loadGraphScript = '
		swfobject.embedSWF(
		  "'.$html->url("/newsletter/swf/open-flash-chart.swf").'", "my_chart", "700", "400",
		  "9.0.0", "expressInstall.swf",
		  {"data-file":"'.$html->url("/admin/newsletter/newsletter_stats/graphs/".$Newsletter['Newsletter']['id']).'"}
		);';
if(!empty($toUpdate)){
	$this->Html->scriptBlock('
		(function( $ ) {
			function loadGraph(){
				if(window.console){
					console.log("embed");
				}
				
			}
			$(function(){
				$.ajax({
				  url: "'.$this->Html->url(array($Newsletter['Newsletter']['id'],'async'=>1)).'",
				}).done(function( data ) {
					var $content = $(data);
					$(".ajaxZone",$content).each(function(){
						$("body #"+$(this).attr("id")).replaceWith($(this));
					})
					$(".loadingStats").hide();
					'.(empty($allviews)?$loadGraphScript:'').'
				});
			})
		})( jQuery );
	',array('inline'=>false));
}elseif(!empty($allviews)){
	$this->Html->scriptBlock($loadGraphScript,array('inline'=>false));
}



$validSended = $sended_count;
if( isset($bounces) ) {
	$validSended -= $bounces;
}
?>

<div class="newsletters stats">
<?php if(!empty($toUpdate)){ ?>
	<div class="loadingStats"><?php __('Statistics are getting updated'); ?></div>
<?php }?>
<h2><?php __d('newsletter','Newsletters Stats');?></h2>
<ul class="info">
	<div class="ajaxZone" id="asyncZone1">
		<li><div class="label"><?php __d('newsletter','Newsletters title');?></div><?php echo $Newsletter['Newsletter']['title']; ?></li>
		<li><div class="label"><?php printf(__d('newsletter','Sended to %d people',true),$sended_count);?></div></li>
		<li><div class="label"><?php __d('newsletter','Views');?></div><?php if(!empty($allviews)) echo $allviews; ?></li>
	</div>
	<?php if(!empty($allviews)){ ?>
		<?php if(isset($ajax)) echo '<div class="ajaxZone" id="asyncZone2">'; ?>
		<div id="my_chart"></div>
		<?php if(isset($ajax)) echo '</div>'; ?>
	<?php }else{ ?>
		<div class="ajaxZone" id="asyncZone2"></div>
	<?php } ?>
	<div class="ajaxZone" id="asyncZone3">
		<li><div class="label"><?php __d('newsletter','Unique views');?></div><?php if(!empty($uniqueviews)) echo $uniqueviews.' ('.($sended_count?round($uniqueviews/$validSended*100, 2):0).'%)' ?></li>
		<li><div class="label"><?php __d('newsletter','Cliqued links');?></div><?php if(!empty($clickedlinks)) echo $clickedlinks ?>

		<br >
		Top urls : 
			<ul>
			
			<?php 
			//debug($toppages);
			foreach($toppages as $top){
				echo "<li>".$top["NewsletterEvent"]['url']." : <b>".$top[0]['count(*)']." clics</b></li>";
			}
			?>
			</ul>
		</li>
		<li><div class="label"><?php __d('newsletter','Unique visits');?></div><?php if(!empty($uniqueclics)) echo $uniqueclics.' ('.($sended_count?round($uniqueclics/$validSended*100, 2):0).'%)' ?></li>
		<?php if( isset($bounces) ) { ?>
			<li><div class="label"><?php __d('newsletter','Email bounced');?></div><?php echo $bounces ?> <?php echo $html->link(__d('newsletter','list', true), array('plugin'=>'newsletter','controller'=>'newsletter_events','action' => 'index','e'=>'bounce','newsletter'=>$Newsletter['Newsletter']['id']));?>
		<?php }?>
	</div>
</ul>
</div>
<div class="actions">
	<ul>
		<li><?php echo $html->link(__d('newsletter','Generate Excel', true), array('action' => 'excel'));?></li>
		<li><?php echo $html->link(__d('newsletter','Back to Newsletters List', true), array('action' => 'index'));?></li>
	</ul>
</div>
