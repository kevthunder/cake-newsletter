<div class="newsletterEmails view">
<h2><?php  __('NewsletterEmail');?></h2>
	<dl><?php $i = 0; $class = ' class="altrow"';?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Id'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $newsletterEmail['NewsletterEmail']['id']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Active'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $newsletterEmail['NewsletterEmail']['active']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Created'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $newsletterEmail['NewsletterEmail']['created']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Modified'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $newsletterEmail['NewsletterEmail']['modified']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Name'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $newsletterEmail['NewsletterEmail']['name']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Email'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $newsletterEmail['NewsletterEmail']['email']; ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<ul>
		<li><?php echo $html->link(__('Edit NewsletterEmail', true), array('action' => 'edit', $newsletterEmail['NewsletterEmail']['id'])); ?> </li>
		<li><?php echo $html->link(__('Delete NewsletterEmail', true), array('action' => 'delete', $newsletterEmail['NewsletterEmail']['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $newsletterEmail['NewsletterEmail']['id'])); ?> </li>
		<li><?php echo $html->link(__('List NewsletterEmails', true), array('action' => 'index')); ?> </li>
		<li><?php echo $html->link(__('New NewsletterEmail', true), array('action' => 'add')); ?> </li>
	</ul>
</div>
