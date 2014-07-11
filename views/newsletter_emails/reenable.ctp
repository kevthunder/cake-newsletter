<?php if( !empty($test) ) { ?>
	<div class="warning"><?php __d('newsletter','This is only a test, no emails where actually enabled or disabled.') ?></div>
<?php }?>


<h1><?php __d('newsletter','Thank you') ?></h1>
<?php
	//debug($sended);
	if(!empty($sended['Newsletter']['data']['reenable_text'])){
		echo $sended['Newsletter']['data']['reenable_text'];
	}
?>