<?php 
	$html->css('/newsletter/css/newsletter.admin',null,array('inline'=>false));
	$orderedSendlist = NewsletterConfig::load('orderedSendlist');
?>
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
	<th><?php __d('newsletter','Email count');?></th>
	<?php if( $orderedSendlist ) { ?>
	<th><?php echo $paginator->sort('order');?></th>
	<?php }?>
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
			<?php 
			if(strtotime($newsletterSendlist['NewsletterSendlist']['created']) != 0){
				echo date_('jS F Y',strtotime($newsletterSendlist['NewsletterSendlist']['created'])); 
			}
			?>
		</td>
		<td>
			<?php echo $newsletterSendlist['NewsletterSendlist']['nb_email']; ?>
		</td>
		<?php if( $orderedSendlist ) { ?>
		<td class="order labeled">
			<?php
				$ordered = (!empty($this->params['paging']['NewsletterSendlist']['options']['order']) && key($this->params['paging']['NewsletterSendlist']['options']['order']) == 'NewsletterSendlist.order' );
			
				if($ordered) echo $html->link(
					'<span>'.__d('newsletter','Move Up', true).'</span>', 
					array('action' => 'up', $newsletterSendlist['NewsletterSendlist']['id']), 
					array('class'=>'icon up','escape' => false)
				);
				echo '<div class="curOrder">'.$newsletterSendlist['NewsletterSendlist']['order'].'</div>';
				if($ordered) echo $html->link(
					'<span>'.__d('newsletter','Move Down', true).'</span>', 
					array('action' => 'down', $newsletterSendlist['NewsletterSendlist']['id']), 
					array('class'=>'icon down','escape' => false)
				);
			?>
		</td>
		<?php } ?>
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
					'<span>'.__d('newsletter','Add Email', true).'</span>', 
					array('plugin'=>'newsletter', 'controller'=>'newsletter_emails', 'action' => 'add', 'list_id'=>$newsletterSendlist['NewsletterSendlist']['id']),
					array('class'=>'icon add','escape' => false)
				);
			}
			echo $html->link(
				'<span>'.__d('newsletter','Export to Excel', true).'</span>', 
				array('action' => 'xls', $newsletterSendlist['NewsletterSendlist']['id']), 
				array('class'=>'icon excel','escape' => false)
			);
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
