<div class="newsletterSendlists index">
<h2><?php __('NewsletterSendlists');?></h2>
<p>
<?php
echo $paginator->counter(array(
'format' => __('Page %page% of %pages%, showing %current% records out of %count% total, starting on record %start%, ending on %end%', true)
));
?></p>
<table cellpadding="0" cellspacing="0">
<tr>
	<th><?php echo $paginator->sort('id');?></th>
	<th><?php echo $paginator->sort('active');?></th>
	<th><?php echo $paginator->sort('created');?></th>
	<th><?php echo $paginator->sort('modified');?></th>
	<th><?php echo $paginator->sort('title');?></th>
	<th><?php echo $paginator->sort('description');?></th>
	<th class="actions"><?php __('Actions');?></th>
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
			<?php echo $newsletterSendlist['NewsletterSendlist']['active']; ?>
		</td>
		<td>
			<?php echo $newsletterSendlist['NewsletterSendlist']['created']; ?>
		</td>
		<td>
			<?php echo $newsletterSendlist['NewsletterSendlist']['modified']; ?>
		</td>
		<td>
			<?php echo $newsletterSendlist['NewsletterSendlist']['title']; ?>
		</td>
		<td>
			<?php echo $newsletterSendlist['NewsletterSendlist']['description']; ?>
		</td>
		<td class="actions">
			<?php echo $html->link(__('View', true), array('action' => 'view', $newsletterSendlist['NewsletterSendlist']['id'])); ?>
			<?php echo $html->link(__('Edit', true), array('action' => 'edit', $newsletterSendlist['NewsletterSendlist']['id'])); ?>
			<?php echo $html->link(__('Delete', true), array('action' => 'delete', $newsletterSendlist['NewsletterSendlist']['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $newsletterSendlist['NewsletterSendlist']['id'])); ?>
		</td>
	</tr>
<?php endforeach; ?>
</table>
</div>
<div class="paging">
	<?php echo $paginator->prev('<< '.__('previous', true), array(), null, array('class'=>'disabled'));?>
 | 	<?php echo $paginator->numbers();?>
	<?php echo $paginator->next(__('next', true).' >>', array(), null, array('class' => 'disabled'));?>
</div>
<div class="actions">
	<ul>
		<li><?php echo $html->link(__('New NewsletterSendlist', true), array('action' => 'add')); ?></li>
	</ul>
</div>
