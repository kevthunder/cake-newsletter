<?php
	$html->css('/newsletter/css/newsletter.admin',null,array('inline'=>false));
	$this->Paginator->options(array('url' => $this->passedArgs));
?>
<div class="newsletterSendings index">
	<?php
		echo $this->Form->create('Newsletter Sending', array('class' => 'search', 'url' => array('action' => 'index')));
		echo $this->Form->input('q', array('class' => 'keyword', 'label' => false, 'after' => $form->submit(__('Search', true), array('div' => false))));
		echo $this->Form->end();
	?>	
	<?php  ?>
	<h2>
	<?php
		if( !empty($scheduled) ) {
			__d('newsletter','Scheduled Sendings list');
		}elseif( !empty($pending) ) {
			__d('newsletter','Incomplete Sendings list');
		}else{
			__d('newsletter','Newsletter Sendings');
		}
	?>
	</h2>
	
	<table cellpadding="0" cellspacing="0">
		<tr>
			<th><?php echo $this->Paginator->sort('id');?></th>		
			<th><?php echo $this->Paginator->sort('date');?></th>
			<?php if( empty($newsletter) ) { ?>
			<th><?php echo $this->Paginator->sort('newsletter_id');?></th>			
			<?php }?>
			<th><?php echo $this->Paginator->sort(__d('newsletter','Selected Sendlist(s)',true),'selected_lists');?></th>			
			<th><?php echo $this->Paginator->sort(__d('newsletter','Additional Email(s)',true),'additional_emails');?></th>		
			<th><?php echo $this->Paginator->sort(__d('newsletter','Status',true),'status');?></th>
			<?php if( empty($pending) ) { ?>
			<th><?php echo $this->Paginator->sort(__d('newsletter','Started',true),'started');?></th>
			<th><?php echo $this->Paginator->sort(__d('newsletter','Confirmed',true),'confirm');?></th>
			<?php }?>	
			<th><?php __d('newsletter','Failed deliveries');?></th>
			<th><?php __d('newsletter','Completion (%)');?></th>
			<th class="actions"><?php __('Actions');?></th>
		</tr>
		<?php
			$i = 0;
			$bool = array(__('No', true), __('Yes', true), null => __('No', true));
			foreach ($newsletterSendings as $newsletterSending) {
				$class = null;
				if ($i++ % 2 == 0) {
					$class = ' class="altrow"';
				}
				?>
					<tr<?php echo $class;?>>
						<td class="id"><?php echo $newsletterSending['NewsletterSending']['id']; ?>&nbsp;</td>
						<td class="date">
			<?php 
			if(strtotime($newsletterSending['NewsletterSending']['date']) != 0){
				echo date_('jS F Y, G\hi',strtotime($newsletterSending['NewsletterSending']['date'])); 
			}
			?></td>
						<?php if( empty($newsletter) ) { ?>
						<td>
							<?php echo $this->Html->link($newsletterSending['Newsletter']['title'], array('plugin'=>'newsletter','controller' => 'newsletters', 'action' => 'edit', $newsletterSending['Newsletter']['id'])); ?>
						</td>	
						<?php }?>
						<td class="selected_lists"><?php 
							if(!empty($newsletterSending['NewsletterSending']['selected_lists'])){
								foreach($newsletterSending['NewsletterSending']['selected_lists'] as $i => $list){
									if($i != 0) echo ' ,';
									if(!empty($sendlists[$list])){
										echo '<a href="'.$this->Html->url(array('plugin'=>'newsletter','controller'=>'newsletter_emails','index'=>'view',$sendlists[$list])).'">'.$sendlists[$list].'</a>';
									}else{
										echo '('.__d('newsletter','Deleted sendlist',true).')';
									}
								}
							} ?>&nbsp;</td>
						<td class="additional_emails"><?php echo $text->truncate($newsletterSending['NewsletterSending']['additional_emails'], 150, array('exact' => false)); ?>&nbsp;</td>
						<td class="status"><?php 
							$status = $newsletterSending['NewsletterSending']['status'];
							if($newsletterSending['NewsletterSending']['started'] === '0') $status = 'paused';
							if(strtotime($newsletterSending['NewsletterSending']['date']) > mktime()) $status = 'waiting';
							__d('newsletter',Inflector::humanize($status));
						?>&nbsp;</td>
						<?php if( empty($pending) ) { ?>
						<td class="started"><?php echo $bool[$newsletterSending['NewsletterSending']['started']]; ?>&nbsp;</td>
						<td class="confirm"><?php echo $bool[$newsletterSending['NewsletterSending']['confirm']]; ?>&nbsp;</td>
						<?php }?>	
						<td class="started"><?php echo $newsletterSending['NewsletterSending']['errors']; ?>&nbsp;</td>
						<td class="confirm"><?php 
							$total = $newsletterSending['NewsletterSending']['total_sended']+$newsletterSending['NewsletterSending']['remaining'];
							if($total > 0){
								echo $newsletterSending['NewsletterSending']['total_sended'].'/'.$total.' ('.number_format($newsletterSending['NewsletterSending']['total_sended']/$total*100,2).' %)'; 
							}else{
								echo '0%';
							}
						?>&nbsp;</td>
						<td class="actions labeled">
						<?php
							echo $html->link(
								'<span>'.__d('newsletter','Continue', true).'</span>', 
								array('action' => 'send', $newsletterSending['NewsletterSending']['id']), 
								array('class'=>'icon continue','escape' => false)
							);
							if($newsletterSending['NewsletterSending']['scheduled'] && strtotime($newsletterSending['NewsletterSending']['date']) > mktime()){
								echo $html->link(
									'<span>'.__d('newsletter','Edit', true).'</span>', 
									array('action' => 'edit', $newsletterSending['NewsletterSending']['id']), 
									array('class'=>'icon edit','escape' => false)
								);
							}
							echo $html->link(
								'<span>'.__d('newsletter','Cancel', true).'</span>', 
								array('action' => 'cancel', $newsletterSending['NewsletterSending']['id']), 
								array('class'=>'icon delete','escape' => false)
							);
						?>
						</td>
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
		<?php if( !empty($newsletter) ) { ?>	
		<li><?php echo $this->Html->link(__d('newsletter','New Newsletter sending', true), array('action' => 'add',$newsletter['Newsletter']['id'])); ?></li>
		<?php }?>
		<li><?php echo $this->Html->link(__d('newsletter','Back to Newsletters List', true), array('plugin'=>'newsletter','controller' => 'newsletters', 'action' => 'index')); ?> </li>
	</ul>
</div>