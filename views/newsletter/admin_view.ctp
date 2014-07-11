<?php 
$this->NewsletterMaker->getLib('jquery');
//$javascript->link('/newsletter/js/jquery-1.3.2.min.js', false);

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
<?php 
	$content = $Newsletter['Newsletter']['html'];
	if(preg_match('/<body[^>]*>/',$content,$matches,PREG_OFFSET_CAPTURE)){
		$content = substr($content,$matches[0][1] + strlen($matches[0][0]));
	}
	$content = str_replace(array('<html>','</html>','<body>','</body>','%sended_id%','%code%'),'',$content); 
	echo $content;
?>