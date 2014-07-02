<?php 
/* SVN FILE: $Id$ */
/* Newsletter schema generated on: 2014-02-06 16:02:09 : 1391723049*/
class NewsletterSchema extends CakeSchema {
	var $name = 'Newsletter';

	function before($event = array()) {
		return true;
	}

	function after($event = array()) {
	}

	var $newsletter_assocs = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'type' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'my_newsletter_id' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'newsletter_id' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_unicode_ci', 'engine' => 'MyISAM')
	);
	var $newsletter_boxes = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'active' => array('type' => 'boolean', 'null' => false, 'default' => NULL),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
		'modified' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
		'newsletter_id' => array('type' => 'integer', 'null' => false, 'default' => NULL),
		'zone' => array('type' => 'string', 'null' => false, 'default' => NULL),
		'order' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'template' => array('type' => 'string', 'null' => false, 'default' => NULL),
		'data' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'multimedia' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_unicode_ci', 'engine' => 'MyISAM')
	);
	var $newsletter_emails = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'active' => array('type' => 'boolean', 'null' => false, 'default' => NULL),
		'name' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'email' => array('type' => 'string', 'null' => false, 'default' => NULL),
		'user_action' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
		'modified' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_unicode_ci', 'engine' => 'MyISAM')
	);
	var $newsletter_sended = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'newsletter_id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'index'),
		'newsletter_variant_id' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'email_id' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'tabledlist_id' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'email' => array('type' => 'string', 'null' => false, 'default' => NULL, 'key' => 'index'),
		'name' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'code' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'view' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'sending_id' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'status' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'error' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'date' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'active' => array('type' => 'boolean', 'null' => true, 'default' => NULL),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
		'modified' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'newsletter_id' => array('column' => 'newsletter_id', 'unique' => 0), 'email' => array('column' => 'email', 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_unicode_ci', 'engine' => 'MyISAM')
	);
	var $newsletter_sendings = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'newsletter_id' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'selected_lists' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'additional_emails' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'check_sended' => array('type' => 'boolean', 'null' => true, 'default' => NULL),
		'date' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'scheduled' => array('type' => 'boolean', 'null' => true, 'default' => NULL),
		'html' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'sender_name' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'sender_email' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'data' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'wrapper' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'self_sending' => array('type' => 'boolean', 'null' => true, 'default' => NULL),
		'status' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'started' => array('type' => 'boolean', 'null' => true, 'default' => NULL),
		'confirm' => array('type' => 'boolean', 'null' => true, 'default' => NULL),
		'active' => array('type' => 'boolean', 'null' => true, 'default' => NULL),
		'last_process_time' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'console' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_unicode_ci', 'engine' => 'MyISAM')
	);
	var $newsletter_sendlists = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'active' => array('type' => 'boolean', 'null' => false, 'default' => NULL),
		'subscriptable' => array('type' => 'boolean', 'null' => false, 'default' => NULL),
		'order' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
		'modified' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
		'title' => array('type' => 'string', 'null' => false, 'default' => NULL),
		'description' => array('type' => 'text', 'null' => false, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_unicode_ci', 'engine' => 'MyISAM')
	);
	var $newsletter_sendlists_emails = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'newsletter_sendlist_id' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'newsletter_email_id' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'active' => array('type' => 'boolean', 'null' => true, 'default' => '1'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'newsletter_sendlist_id' => array('column' => array('newsletter_sendlist_id', 'newsletter_email_id'), 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_unicode_ci', 'engine' => 'MyISAM')
	);
	var $newsletter_events = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'sended_id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'index'),
		'date' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
		'action' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'url' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'ip_address' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'user_agent' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'processed' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'sended_id' => array('column' => 'sended_id', 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_unicode_ci', 'engine' => 'MyISAM')
	);
	var $newsletter_stats = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'newsletter_id' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'date' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'name' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'val' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'context' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
		'modified' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'newsletter_id' => array('column' => 'newsletter_id', 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_unicode_ci', 'engine' => 'MyISAM')
	);
	var $newsletter_variants = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'newsletter_id' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'code' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 63),
		'conditions' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'html' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'active' => array('type' => 'boolean', 'null' => true, 'default' => NULL),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_unicode_ci', 'engine' => 'MyISAM')
	);
	var $newsletters = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'active' => array('type' => 'boolean', 'null' => false, 'default' => NULL),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
		'modified' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
		'title' => array('type' => 'string', 'null' => false, 'default' => NULL),
		'date' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
		'lang' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'sender' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'html' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'renderers_sha' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'template' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'data' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'tested' => array('type' => 'boolean', 'null' => false, 'default' => NULL),
		'external_key' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'last_sync' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'cache_file' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_unicode_ci', 'engine' => 'MyISAM')
	);
}
?>