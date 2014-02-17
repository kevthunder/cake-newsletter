
<div class="newsletterEvents index">
	<?php
		echo $this->Form->create('Newsletter Event', array('class' => 'search', 'url' => array('action' => 'index')));
		echo $this->Form->input('q', array('class' => 'keyword', 'label' => false, 'after' => $form->submit(__('Search', true), array('div' => false))));
		echo $this->Form->end();
		
		$showAction = empty($this->passedArgs['e']) ;
		$showUrl = ( empty($this->passedArgs['e']) || $this->passedArgs['e'] == 'click' );
		$showClient = ( empty($this->passedArgs['e']) || $this->passedArgs['e'] != 'bounce' );
	?>	
	<h2><?php __('Newsletter Events');?></h2>
	
	<table cellpadding="0" cellspacing="0">
		<tr>
			<th><?php echo $this->Paginator->sort('id');?></th>			
			<th><?php echo $this->Paginator->sort(__('Email',true),'NewsletterSended.email');?></th>			
			<th><?php echo $this->Paginator->sort('date');?></th>	
			<?php if($showAction) { ?>		
			<th><?php echo $this->Paginator->sort('action');?></th>	
			<?php }?>
			<?php if($showUrl) { ?>
			<th><?php echo $this->Paginator->sort('url');?></th>
			<?php }?>
			<?php if($showClient) { ?>
			<th><?php echo $this->Paginator->sort('ip_address');?></th>			
			<th><?php echo $this->Paginator->sort('user_agent');?></th>	
			<?php }?>
			
			<?php /* <th class="actions"><?php __('Actions');?></th> */ ?>
		</tr>
		<?php
			$i = 0;
			$bool = array(__('No', true), __('Yes', true), null => __('No', true));
			foreach ($newsletterEvents as $newsletterEvent) {
				$class = null;
				if ($i++ % 2 == 0) {
					$class = ' class="altrow"';
				}
				?>
					<tr<?php echo $class;?>>
						<td class="id"><?php echo $newsletterEvent['NewsletterEvent']['id']; ?>&nbsp;</td>
						<td>
						<?php 
							if(empty($newsletterEvent['NewsletterSended']['tabledlist_id']) && !empty($newsletterEvent['NewsletterSended']['email_id'])){
								echo $this->Html->link($newsletterEvent['NewsletterSended']['email'], array('controller' => 'newsletter_emails', 'action' => 'edit', $newsletterEvent['NewsletterSended']['email_id']));
							}else{
								echo $newsletterEvent['NewsletterSended']['email'];
							}
						?>
						</td>
						<td class="date"><?php echo $newsletterEvent['NewsletterEvent']['date']; ?>&nbsp;</td>
						<?php if($showAction) { ?>		
						<td class="action"><?php echo $newsletterEvent['NewsletterEvent']['action']; ?>&nbsp;</td>
						<?php }?>
						<?php if($showUrl) { ?>
						<td class="url"><?php echo $newsletterEvent['NewsletterEvent']['url']; ?>&nbsp;</td>
						<?php }?>
						<?php if($showClient) { ?>
						<td class="ip_address"><?php echo $newsletterEvent['NewsletterEvent']['ip_address']; ?>&nbsp;</td>
						<td class="user_agent"><?php echo $newsletterEvent['NewsletterEvent']['user_agent']; ?>&nbsp;</td>
						<?php }?>
						<?php /*
						<td class="actions">
						</td>
						*/ ?>
					</tr>
				<?php
			}
		?>
	</table>
	
	<p class="paging">
		<?php
			echo $this->Paginator->counter(array(
				'format' => __('Page %page% of %pages%, showing %current% records out of %count% total, starting on record %start%, ending on %end%', true)
			));
		?>	</p>

	<div class="paging">
		<?php echo $this->Paginator->prev('« '.__('previous', true), array(), null, array('class'=>'disabled'));?>
 |
		<?php echo $this->Paginator->numbers();?>
 |
		<?php echo $this->Paginator->next(__('next', true).' »', array(), null, array('class' => 'disabled'));?>
	</div>
</div>
<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(sprintf(__('New %s', true), __('Newsletter Event', true)), array('action' => 'add')); ?></li>		<li><?php echo $this->Html->link(sprintf(__('List %s', true), __('Newsletter Sendeds', true)), array('controller' => 'newsletter_sendeds', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(sprintf(__('New %s', true), __('Newsletter Sended', true)), array('controller' => 'newsletter_sendeds', 'action' => 'add')); ?> </li>
	</ul>
</div>