<?php
$javascript->link('/newsletter/js/jquery-1.4.2.min.js', false);
$script = '
$(function(e){
	$(".sendto_link").click(function(e){
		$(this).closest(".actions").find(".send_lists").show();
		return false;
	});
});
';
$javascript->codeBlock($script,array('inline'=>false));


//debug($newsletters);
?>
<?php $html->css('/newsletter/css/newsletter.admin',null,array('inline'=>false)); ?>
<div class="newsletters index">
<h2><?php __d('newsletter','Newsletters');?></h2>
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
	<th><?php echo $paginator->sort('date');?></th>
	<th><?php echo $paginator->sort('template');?></th>
	<th><?php echo $paginator->sort('active');?></th>
	<th><?php echo $paginator->sort('created');?></th>
	<th><?php echo $paginator->sort('modified');?></th>
	<th class="actions"><?php __d('newsletter','Actions');?></th>
</tr>
<?php
$i = 0;
foreach ($newsletters as $newsletter):
	$class = null;
	if ($i++ % 2 == 0) {
		$class = ' class="altrow"';
	}
?>
	<tr<?php echo $class;?>>
		<td>
			<?php echo $newsletter['Newsletter']['id']; ?>
		</td>
		<td>
			<?php echo $newsletter['Newsletter']['title']; ?>
		</td>
		<td>
			<?php echo $newsletter['Newsletter']['date']; ?>
		</td>
		<td>
			<?php echo $newsletter['Newsletter']['TemplateConfig']->getLabel(); ?>
		</td>
		<td>
			<?php echo $newsletter['Newsletter']['active']; ?>
		</td>
		<td>
			<?php echo $newsletter['Newsletter']['created']; ?>
		</td>
		<td>
			<?php echo $newsletter['Newsletter']['modified']; ?>
		</td>
		<td class="actions">
        	<?php echo $html->link(__d('newsletter','Send', true), array('plugin'=>'newsletter', 'controller'=>'newsletter_sendings', 'action' => 'add', $newsletter['Newsletter']['id'])); ?>
        	<?php echo $html->link(__d('newsletter','Send to', true), array('plugin'=>'newsletter', 'controller'=>'newsletter_sendings', 'action' => 'add', $newsletter['Newsletter']['id']),array('class'=>'sendto_link')); ?>
            <div class="send_lists" style="display:none">
            	<h1>Listes de diffusion :</h1>
                <ul>
                <?php foreach ($sendlists as $sendlist): ?>
                	<li><?php echo $html->link($sendlist['NewsletterSendlist']['title'], array('plugin'=>'newsletter', 'controller'=>'newsletter_sendings', 'action' => 'add', $newsletter['Newsletter']['id'],$sendlist['NewsletterSendlist']['id']),null, sprintf(__d('newsletter','Êtes-vous sûr de vouloir envoyer la newsletter "%s" à "%s" ?', true), $newsletter['Newsletter']['title'],$sendlist['NewsletterSendlist']['title'])); ?></li>
				<?php endforeach; ?>
                </ul>
            </div>
			<?php echo $html->link(__d('newsletter','View', true), array('action' => 'view', $newsletter['Newsletter']['id'])); ?>
			<?php echo $html->link(__d('newsletter','Excel', true), array('action' => 'excel', $newsletter['Newsletter']['id'])); ?>
			<?php echo $html->link(__d('newsletter','Edit', true), array('action' => 'edit', $newsletter['Newsletter']['id'])); ?>
            <?php echo $html->link(__d('newsletter','Stats', true), array('action' => 'stats', $newsletter['Newsletter']['id'])); ?>
			<?php echo $html->link(__d('newsletter','Delete', true), array('action' => 'delete', $newsletter['Newsletter']['id']), null, sprintf(__d('newsletter','Are you sure you want to delete # %s?', true), $newsletter['Newsletter']['id'])); ?>
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
		<li><?php echo $html->link(__d('newsletter','New Newsletter', true), array('action' => 'add')); ?></li>
	</ul>
</div>
