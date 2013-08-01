<?php 
$this->NewsletterMaker->getLib('jquery');
//$javascript->link('/newsletter/js/jquery-1.9.1.js', false);

$script = '$(function(e){
				$("#print_bt").click(function(e){
					window.print();
				});
			});';
$javascript->codeBlock($script,array('inline'=>false));
$html->css('/newsletter/css/newsletter',null,array('inline'=>false));
?>
<div id="header" class="noprint">
	<div id="print_bt" class="btn">Imprimer</div>
    <br style="clear:both;"/>
</div>
<?php echo str_replace(array('<html>','</html>','<body>','</body>','%sended_id%'),'',$Newsletter['Newsletter']['html']); ?>