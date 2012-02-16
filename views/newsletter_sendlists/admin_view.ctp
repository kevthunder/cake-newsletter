<div class="newsletterSendlists view">
<h2><?php  __('NewsletterSendlist');?></h2>
	<dl><?php $i = 0; $class = ' class="altrow"';?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Id'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $newsletterSendlist['NewsletterSendlist']['id']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Active'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $newsletterSendlist['NewsletterSendlist']['active']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Created'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $newsletterSendlist['NewsletterSendlist']['created']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Modified'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $newsletterSendlist['NewsletterSendlist']['modified']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Title'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $newsletterSendlist['NewsletterSendlist']['title']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Description'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $newsletterSendlist['NewsletterSendlist']['description']; ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<ul>
		<li><?php echo $html->link(__('Edit NewsletterSendlist', true), array('action' => 'edit', $newsletterSendlist['NewsletterSendlist']['id'])); ?> </li>
		<li><?php echo $html->link(__('Delete NewsletterSendlist', true), array('action' => 'delete', $newsletterSendlist['NewsletterSendlist']['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $newsletterSendlist['NewsletterSendlist']['id'])); ?> </li>
		<li><?php echo $html->link(__('List NewsletterSendlists', true), array('action' => 'index')); ?> </li>
		<li><?php echo $html->link(__('New NewsletterSendlist', true), array('action' => 'add')); ?> </li>
	</ul>
</div>
