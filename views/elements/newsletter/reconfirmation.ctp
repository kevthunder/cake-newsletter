<h1><?php echo $title_for_newsletter ?></h1>
<table width="700" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td><?php echo $newsletterMaker->column('1'); ?></td>
  </tr>
  </tr>
	<td>
		<?php 
			echo $this->NewsletterMaker->single(1,'reenable_links',array(
				'link_yes'=>__d('newsletter','I wish to continue receiving Emails',true),
				'link_no'=>__d('newsletter','I do not wish to be contacted anymore',true)
			));
		?>
	</td>
  <tr>
  <tr>
    <td><?php echo $newsletterMaker->column('2'); ?></td>
  </tr>
</table>