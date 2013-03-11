<?php $html->css('/newsletter/css/newsletter.admin',null,array('inline'=>false)); ?>
<div class="newsletterSendlists index">
<h2><?php __d('newsletter','NewsletterSendlists');?></h2>
<p>
<?php
echo $paginator->counter(array(
'format' => __d('newsletter','Page %page% of %pages%, showing %current% records out of %count% total, starting on record %start%, ending on %end%', true)
));
?></p>
<table cellpadding="0" cellspacing="0">
<tr>
	<th><?php echo $paginator->sort('id');?></th>
	<th><?php echo $paginator->sort('title');?></th>
	<th><?php echo $paginator->sort('description');?></th>
	<th><?php echo $paginator->sort('active');?></th>
	<th><?php echo $paginator->sort(__d('newsletter','Created',true),'created');?></th>
	<th><?php echo $paginator->sort(__d('newsletter','Modified',true),'modified');?></th>
	<th><?php __d('newsletter','Email count');?></th>
	<th class="actions"><?php __d('newsletter','Actions');?></th>
</tr>
<?php
$i = 0;
foreach ($newsletterSendlists as $newsletterSendlist):
	$class = null;
	if ($i++ % 2 == 0) {
		$class = ' class="altrow"';
	}
?>
	<tr<?php echo $class;?>>
		<td>
			<?php echo $newsletterSendlist['NewsletterSendlist']['id']; ?>
		</td>
		<td>
			<?php echo $newsletterSendlist['NewsletterSendlist']['title']; ?>
		</td>
		<td>
			<?php echo $newsletterSendlist['NewsletterSendlist']['description']; ?>
		</td>
		<td>
			<?php echo $newsletterSendlist['NewsletterSendlist']['active']; ?>
		</td>
		<td>
			<?php echo $newsletterSendlist['NewsletterSendlist']['created']; ?>
		</td>
		<td>
			<?php echo $newsletterSendlist['NewsletterSendlist']['modified']; ?>
		</td>
		<td>
			<?php echo $newsletterSendlist['NewsletterSendlist']['nb_email']; ?>
		</td>
		<td class="actions labeled">
		<?php
			echo $html->link(
				'<span>'.__d('newsletter','Edit', true).'</span>', 
				array('action' => 'edit', $newsletterSendlist['NewsletterSendlist']['id']), 
				array('class'=>'icon edit','escape' => false)
			);
			echo $html->link(
				'<span>'.__d('newsletter','View Emails List', true).'</span>', 
				array('plugin'=>'newsletter', 'controller'=>'newsletter_emails','action' => 'index', $newsletterSendlist['NewsletterSendlist']['id']),
				array('class'=>'icon view','escape' => false)
			);
			if(!in_array($newsletterSendlist['NewsletterSendlist']['id'],$restrictedSendlists)){
				echo $html->link(
					'<span>'.__d('newsletter','Delete', true).'</span>', 
					array('action' => 'delete', $newsletterSendlist['NewsletterSendlist']['id']), 
					array('class'=>'icon delete','escape' => false)
				);
				echo $html->link(
					'<span>'.__d('newsletter','Nouveau Email', true).'</span>', 
					array('plugin'=>'newsletter', 'controller'=>'newsletter_emails', 'action' => 'add', 'list_id'=>$newsletterSendlist['NewsletterSendlist']['id']),
					array('class'=>'icon add','escape' => false)
				);
			}
		?>
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
		<li><?php echo $html->link(__d('newsletter','New NewsletterSendlist', true), array('action' => 'add')); ?></li>
		<li><?php echo $html->link(__d('newsletter','Importation', true), array('action' => 'import')); ?></li>
	</ul>
</div>
