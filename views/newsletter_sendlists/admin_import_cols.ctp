<div class="newsletterSendlists form">
<?php
	$html->css('/newsletter/css/newsletter.admin',null,array('inline'=>false));
	$this->Html->scriptBlock('
		(function( $ ) {
			$(function(){
				testNewList();
				testFirstLine();
				$(\'#NewsletterSendlistListId\').change(testNewList);
				$(\'#NewsletterSendlistImportFirstRow\').change(testFirstLine);
				$(\'#NewsletterSendlistImportFirstRow\').click(testFirstLine);
			})
			function testNewList(){
				var val = $(\'#NewsletterSendlistListId\').val()
				var $input = $(\'#NewsletterSendlistNewList\').closest(\'div.input\');
				if(val == ""){
					$input.show();
				}else{
					$input.hide();
				}
			}
			function testFirstLine(){
				var val = $(\'#NewsletterSendlistImportFirstRow:checked\').length;
				var $row = $(\'#ImportMappingTable tr.firstrow\');
				if(val){
					$row.show();
				}else{
					$row.hide();
				}
			}
		})( jQuery );
	',array('inline'=>false));
?>
<?php echo $form->create('NewsletterSendlist',array('type'=>'file'));?>
	<fieldset>
 		<legend><?php __d('newsletter','Import NewsletterSendlist');?></legend>
		<?php echo $this->Form->input('list_id',array(
			'label'=>__d('newsletter','List were emails will be added',true),
			'empty'=>'('.__d('newsletter','Create New List',true).')',
		)); ?>
		<?php echo $this->Form->input('new_list',array(
			'label'=>__d('newsletter','Sendlist title',true),
			'default'=>$this->data['NewsletterSendlist']['filename']
		)); ?>
        <?php echo $this->Form->input('filename',array('type' => 'hidden')); ?>
		
		<h3><?php __d('newsletter','Columns mapping') ?></h3>
        <?php 
		if(!array_key_exists('import_first_row',$this->data['NewsletterSendlist'])){
			$this->Form->data['NewsletterSendlist']['import_first_row'] = $showFirst;
		}
		echo $this->Form->input('import_first_row',array(
			'label' => __d('newsletter','Import first row',true),
			'type' => 'checkbox',
			'div'=>array('class'=>'input checkbox importFirstRow'))
		); ?>
        <?php $first_line = $teaser[0]; ?>
		<table id="ImportMappingTable">
			<tr>
				<?php foreach ($first_line as $key => $val) { ?>
				<th>
					<?php 
						if(!empty($cols[$key])){
							$this->data['NewsletterSendlist']['cols'][$key] = $cols[$key];
						}
						echo $this->Form->input('NewsletterSendlist.cols.'.$key,array(
							'label'=>false,
							'options' => $fields,
							'empty'=>'('.__d('newsletter','Ignore',true).')',
							'default'=>!empty($cols[$key])?$cols[$key]:null,
						)); 
					?>
				</th>
				<?php } ?>
			</tr>
			<?php foreach ($teaser as $i => $row) {
				$class = null;
				if ($i % 2 == 0) {
					$class[] = 'altrow';
				}
				if($i==0){
					$class[] = 'firstrow';
				}
				$i++;
			?>
			<tr <?php if(!empty($class)) echo 'class="'.implode(' ',$class).'"' ?>>
				<?php foreach ($row as $col => $val) { ?>
				<td><?php echo $val ?></td>
				<?php } ?>
			</tr>
			<?php } ?>
			<?php 
				$class = null;
				if ($i % 2 == 0) {
					$class[] = 'altrow';
				}
				$i++;
			?>
			<tr <?php if(!empty($class)) echo 'class="'.implode(' ',$class).'"' ?>>
				<?php foreach ($first_line as $key => $val) { ?>
				<td>...</td>
				<?php } ?>
			</tr>
		</table>
	</fieldset>
<?php echo $form->end(__('Submit',true));?>
</div>