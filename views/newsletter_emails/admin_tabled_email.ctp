<?php 
$urlOptions = array();
if(isset($sendlist)){
	$urlOptions["id"] = $sendlist['NewsletterSendlist']['id'];
}
if(isset($this->data['q'])){
	$urlOptions["q"] = $this->data['q']; 
}
$paginator->options(array('url' => $urlOptions)); 
?>
<div class="newsletterEmails index">
<h2><?php __d('newsletter','NewsletterEmails');
if(isset($sendlist)){
	echo ' - Liste : '.$sendlist['NewsletterSendlist']['title'];
}
?></h2>
<?php
	$option = array('url'=>array('action' => 'index'));
	if(isset($sendlist)){
		$option['url'][] = $sendlist['NewsletterSendlist']['id'];
	}
	echo $form->create(Inflector::singularize($this->name), $option);
	echo $form->input('q', array('style' => 'width:300px', 'label' => false, 'after' => $form->end(array('style' => 'border: 2px #fff outset;', 'label' => 'Recherche', 'div' => false))));
?>
<p>
<?php
echo $paginator->counter(array(
'format' => __d('newsletter','Page %page% of %pages%, showing %current% records out of %count% total, starting on record %start%, ending on %end%', true)
));
?></p>
<table cellpadding="0" cellspacing="0">
<tr>
	<th><?php echo $paginator->sort('id');?></th>
	<th><?php echo $paginator->sort('active');?></th>
	<th><?php echo $paginator->sort('name');?></th>
	<th><?php echo $paginator->sort('email');?></th>
</tr>
<?php
$i = 0;
foreach ($newsletterEmails as $newsletterEmail):
	$class = null;
	if ($i++ % 2 == 0) {
		$class = ' class="altrow"';
	}
?>
	<tr<?php echo $class;?>>
		<td>
			<?php echo $newsletterEmail['NewsletterEmail']['id']; ?>
		</td>
		<td>
			<?php echo $newsletterEmail['NewsletterEmail']['active']; ?>
		</td>
		<td>
			<?php echo $newsletterEmail['NewsletterEmail']['name']; ?>
		</td>
		<td>
			<?php echo $newsletterEmail['NewsletterEmail']['email']; ?>
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
<div class="actions">
	<ul>
        <li><?php echo $html->link('Toutes les listes de diffusion', array('plugin'=>'newsletter','controller' => 'newsletter_sendlists', 'action' => 'index')); ?></li>
	</ul>
</div>
