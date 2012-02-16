<?php echo $newsletterMaker->createEditForm(); ?>
<?php echo $newsletterMaker->editInput("title"); ?>
<?php echo $newsletterMaker->editInput("text",array('type'=>'tinymce')); ?>
<?php echo $newsletterMaker->editInput("url"); ?>
<?php echo $newsletterMaker->editInput("url_text",array('default'=>'Lire la suite')); ?>
<?php echo $newsletterMaker->endEditForm(); ?>
