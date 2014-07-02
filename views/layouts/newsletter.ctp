<?php //header('Content-Type: text/html; charset=iso-8859-1');  ?>
<?php if(strpos($content_for_layout,'<html') === false ) echo '<html>'  ?>
<?php if(strpos($content_for_layout,'<body') === false ) echo '<body>'  ?>
<?php echo str_replace(array('</html>','</body>'),'',$content_for_layout); ?>
<?php 
if(!isset($has_counter_img) || !$has_counter_img){
	echo $newsletterMaker->counterImg();
}
?>
</body>
</html>