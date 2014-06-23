<h1><?php __d('newsletter','Thank you') ?></h1>
<?php
	//debug($sended);
	if(!empty($sended['Newsletter']['data']['reenable_text'])){
		echo $sended['Newsletter']['data']['reenable_text'];
	}
?>