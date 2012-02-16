<?php //header('Content-Type: text/html; charset=iso-8859-1');  ?>
<html>
<body>
<?php echo $content_for_layout; ?>
<?php 
if(!isset($has_counter_img) || !$has_counter_img){
	echo $newsletterMaker->counterImg();
}
?>
</body>
</html>