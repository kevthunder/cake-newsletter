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
<h2>
<?php if(isset($sendlist)) { 
	echo str_replace('%title%',$sendlist['NewsletterSendlist']['title'],__d('newsletter','Emails for the "%title%" sendlist',true));
 }else{
	__d('newsletter','NewsletterEmails');
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
	<th><?php echo $paginator->sort(__d('newsletter','Subscription date',true),'created');?></th>
	<th><?php echo $paginator->sort(__d('newsletter','Name',true),'name');?></th>
	<th><?php echo $paginator->sort('email');?></th>
	<th class="actions"><?php __d('newsletter','Actions');?></th>
</tr>
<?php
$i = 0;
$bool=array(__('No',true),__('Yes',true));
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
			<?php 
				echo $bool[$newsletterEmail['NewsletterEmail']['active']];
				if(!$newsletterEmail['NewsletterEmail']['active'] && !empty($newsletterEmail['NewsletterEmail']['disabled'])){
					echo date_(' (jS F Y)',strtotime($newsletterEmail['NewsletterEmail']['disabled'])); 
				}
			?>
		</td>
		<td>
			<?php 
			if(strtotime($newsletterEmail['NewsletterEmail']['created']) != 0){
				echo date_('jS F Y',strtotime($newsletterEmail['NewsletterEmail']['created'])); 
			}
			?>
		</td>
		<td>
			<?php echo $newsletterEmail['NewsletterEmail']['name']; ?>
		</td>
		<td>
			<?php echo $newsletterEmail['NewsletterEmail']['email']; ?>
		</td>
		<td class="actions">
			<?php echo $html->link(__d('newsletter','View', true), array('action' => 'view', $newsletterEmail['NewsletterEmail']['id'])); ?>
			<?php 
			$action = array('action' => 'edit', $newsletterEmail['NewsletterEmail']['id']);
			if(isset($sendlist)){
				$action['list_id'] = $sendlist['NewsletterSendlist']['id']; 
			}
			echo $html->link(__d('newsletter','Edit', true), $action); ?>
			<?php 
			$action = array('action' => 'delete', $newsletterEmail['NewsletterEmail']['id']);
			if(isset($sendlist)){
				$action['list_id'] = $sendlist['NewsletterSendlist']['id']; 
			}
			echo $html->link(__d('newsletter','Delete', true), $action, null, sprintf(__d('newsletter','Are you sure you want to delete # %s?', true), $newsletterEmail['NewsletterEmail']['id'])); ?>
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
        <li><?php 
		$action = array('action' => 'add');
		if(isset($sendlist)){
			$action['list_id'] = $sendlist['NewsletterSendlist']['id']; 
		}
		echo $html->link(__d('newsletter','New NewsletterEmail', true), $action); ?></li>
        <li><?php echo $html->link(__d('newsletter','Back to Sendlists', true), array('plugin'=>'newsletter','controller' => 'newsletter_sendlists', 'action' => 'index')); ?></li>
	</ul>
</div>
