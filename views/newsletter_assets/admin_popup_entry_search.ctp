<?php $html->css('/newsletter/css/newsletter.admin',null,null,false); ?>
<?php 
	$urlOptions = array();
	$urlOptions[] = $modelName;
	if(isset($this->data['q'])){
		$urlOptions["q"] = $this->data['q']; 
	}
	$paginator->options(array('url' => $urlOptions));
?>
<div class="newsletters index">
<h2><?php __($modelName);?></h2>
<?php
	$option = array('url'=>array('controller' => 'newsletter_assets', 'action' => 'popup_entry_search'));
	$option['url'] = array_merge($urlOptions,$option['url']);
	unset($option['url']['q']);
	echo $form->create('Search', $option);
	echo $form->input('q', array('style' => 'width:300px', 'label' => false, 'after' => $form->submit('Recherche',array('style' => 'border: 2px #fff outset;', 'div' => false))));
	echo $form->end();
?>
<p>
<?php
echo $paginator->counter(array(
'format' => __d('newsletter','Page %page% of %pages%, showing %current% records out of %count% total, starting on record %start%, ending on %end%', true)
));
?></p>
<table cellpadding="0" cellspacing="0">
<tr>
	<?php 
	foreach ($fields as $field){ 
		echo '<th>'.$paginator->sort($field).'</th>';
	}
	?>
	<th class="actions"><?php __d('newsletter','Actions');?></th>
</tr>
<?php
$i = 0;
foreach ($data as $dataLine):
	$class = null;
	if ($i++ % 2 == 0) {
		$class = ' class="altrow"';
	}
?>
	<tr<?php echo $class;?>>
    	<?php 
		foreach ($fields as $field){ 
			echo '<td>'.$dataLine[$modelName][$field].'</td>';
		}
		?>
		<td class="actions">
        	<a class="bt_select" href="#" id="<?php echo $dataLine[$modelName]['id']; ?>"><?php echo __d('newsletter','Select'); ?></a>
        	
		</td>
	</tr>
<?php endforeach; ?>
</table>
</div>
<div class="paging">
	<?php echo $paginator->prev('<< '.__d('newsletter','previous', true), array(), null, array('class'=>'disabled'));?>
 | 	<?php echo $paginator->numbers();?>
	<?php echo $paginator->next(__d('newsletter','next', true).' >>', array(), null, array('class' => 'disabled'));?>
</div>
