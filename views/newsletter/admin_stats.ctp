<?php 
//$Browscap = new Browscap(APP_DIR."/tmp/");

/*function __getAgentStats($views,$lvl){
	$used_stats = array('platform','browser');
	$stats = array();
	$stats_counts = array();
	foreach($views as $view){
		echo "here";
		if($lvl>1){
			$cur_stats = __getAgentStats($view,$lvl-1);
		}else{
			
			$cur_stats = $Browscap->getBrowser($view['user_agent'],true);
			echo "hrrr";
			print_r($cur_stats);
			//$cur_stats = get_browser($view['user_agent'],true);
		}
		foreach($used_stats as $used_stat){
			if(isset($stats[$used_stat])){
				$stats_counts[$used_stat]++;
			}else{
				$stats_counts[$used_stat] = 1;
			}
			if(is_array($cur_stats[$used_stat])){
				foreach($cur_stats[$used_stat] as $value=>$prc){
					if(isset($stats[$used_stat][$value])){
						$stats[$used_stat][$value] += $prc;
					}else{
						$stats[$used_stat][$value] = $prc;
					}
				}
			}else{
				$value = $cur_stats[$used_stat];
				if(isset($stats[$used_stat][$value])){
					$stats[$used_stat][$value]++;
				}else{
					$stats[$used_stat][$value] = 1;
				}
			}
		}
	}
	foreach($used_stats as $used_stat){
		foreach($stats[$used_stat] as $value=>&$nb){
			$nb = $nb/$stats_counts[$used_stat];
		}
	}
	return $stats;
}*/
$ndSended = $sended_count;
$validSended = $sended_count;
if( isset($bounces) ) {
	$validSended -= $bounces;
}
$nbViews = $allviews;
$nbUniqueViews = $uniqueviews;
$nbClick = $clickedlinks;
$nbVisite = $uniqueclics;
?>
<?php $html->css('/newsletter/css/newsletter.admin',null,array('inline'=>false)); ?>

<div class="newsletters stats">
<h2><?php __d('newsletter','Newsletters Stats');?></h2>
<ul class="info">
	<li><div class="label"><?php __d('newsletter','Newsletters title');?></div><?php echo $Newsletter['Newsletter']['title']; ?></li>
    <li><div class="label"><?php printf(__d('newsletter','Sended to %d people',true),$ndSended);?></div></li>
    <li><div class="label"><?php __d('newsletter','Views');?></div><?php echo $nbViews; ?></li>
	<?php if($nbViews>0){ ?>
	<?php $javascript->link('/newsletter/js/swfobject.js', false); ?>
    <div id="my_chart"></div>
<script type="text/javascript">
swfobject.embedSWF(
  "<?php echo $html->url("/newsletter/swf/open-flash-chart.swf") ?>", "my_chart", "700", "400",
  "9.0.0", "expressInstall.swf",
  {"data-file":"<?php echo $html->url("/admin/newsletter/newsletter/graphs/".$Newsletter['Newsletter']['id']) ?>"}
  );
</script>
	<?php } ?>
    <li><div class="label"><?php __d('newsletter','Unique views');?></div><?php echo $nbUniqueViews; ?> (<?php echo ($ndSended?round($nbUniqueViews/$validSended*100, 2):0); ?>%)</li>
    <li><div class="label"><?php __d('newsletter','Cliqued links');?></div><?php echo $nbClick ?>

    <br >
    Top urls : 
    	<ul>
    	
    	<?php 
		//debug($toppages);
		foreach($toppages as $top){
    		echo "<li>".$top["NewsletterStat"]['url']." : <b>".$top[0]['count(*)']." clics</b></li>";
    	}
		?>
    	</ul>
    </li>
    <li><div class="label"><?php __d('newsletter','Unique visits');?></div><?php echo $nbVisite; ?> (<?php echo ($ndSended?round($nbVisite/$validSended*100, 2):0); ?>%)</li>
	<?php if( isset($bounces) ) { ?>
		<li><div class="label"><?php __d('newsletter','Email bounced');?></div><?php echo $bounces ?>
	<?php }?>
    <?php  
    /*ini_get('browscap')
	if(ini_get('browscap')){
		$mailAgentStats = getMailAgentStats($newsletterSended);
		$webAgentStats = getWebAgentStats($newsletterSended);
		$agentStats = getAgentStats($newsletterSended);
	?>
    <li><div class="label"><?php __d('newsletter','Mail Agents');?></div>
    	<table cellspacing="0">
        	<tr>
            	<th><?php __d('newsletter','Agents');?></th>
                <th><?php __d('newsletter','Percent');?></th>
            </tr>
            <?php 
			foreach($mailAgentStats['browser'] as $agent=>$prc){
				echo '<tr>'."\n";
				echo '<td>'.$agent.'</td>'."\n";
				echo '<td>'.($prc*100).'%</td>'."\n";
				echo '</tr>'."\n";
			}
			?>
        </table>
    </li>
    <li><div class="label"><?php __d('newsletter','Web Browser');?></div>
    	<table cellspacing="0">
        	<tr>
            	<th><?php __d('newsletter','Agents');?></th>
                <th><?php __d('newsletter','Percent');?></th>
            </tr>
            <?php 
			foreach($webAgentStats['browser'] as $agent=>$prc){
				echo '<tr>'."\n";
				echo '<td>'.$agent.'</td>'."\n";
				echo '<td>'.($prc*100).'%</td>'."\n";
				echo '</tr>'."\n";
			}
			?>
        </table>
    </li>
    <li><div class="label"><?php __d('newsletter','Platform');?></div>
    	<table cellspacing="0">
        	<tr>
            	<th><?php __d('newsletter','Agents');?></th>
                <th><?php __d('newsletter','Percent');?></th>
            </tr>
            <?php 
			foreach($agentStats['platform'] as $agent=>$prc){
				echo '<tr>'."\n";
				echo '<td>'.$agent.'</td>'."\n";
				echo '<td>'.($prc*100).'%</td>'."\n";
				echo '</tr>'."\n";
			}
			?>
        </table>
    </li>
	<?php 
	}else{ 
    	debug(__d('newsletter','Cant read browser info. Browscap ini directive not set.',true));
    } */ ?>
</ul>
</div>
<div class="actions">
	<ul>
		<li><?php echo $html->link(__d('newsletter','Generate Excel', true), array('action' => 'excel'));?></li>
		<li><?php echo $html->link(__d('newsletter','Back to Newsletters List', true), array('action' => 'index'));?></li>
	</ul>
</div>
