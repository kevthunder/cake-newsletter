<?php echo '<?php'; ?> 
class <?php echo Inflector::classify(Inflector::slug($name)); ?>NewsletterConf extends NewsletterTemplateConfig {
	var $label = '<?php echo $name; ?>';
} ?>