<?php 
/* SVN FILE: $Id$ */
/* App schema generated on: 2011-08-22 21:08:16 : 1314047956*/
class AppSchema extends CakeSchema {
	var $name = 'App';

	function before($event = array()) {
		return true;
	}

	function after($event = array()) {
	}

	var $newsletter_boxes = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'active' => array('type' => 'boolean', 'null' => false, 'default' => NULL),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
		'modified' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
		'newsletter_id' => array('type' => 'integer', 'null' => false, 'default' => NULL),
		'zone' => array('type' => 'string', 'null' => false, 'default' => NULL),
		'order' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'template' => array('type' => 'string', 'null' => false, 'default' => NULL),
		'data' => array('type' => 'text', 'null' => false, 'default' => NULL),
		'multimedia' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_unicode_ci', 'engine' => 'MyISAM')
	);
	var $newsletter_emails = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'active' => array('type' => 'boolean', 'null' => false, 'default' => NULL),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
		'modified' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
		'sendlist_id' => array('type' => 'integer', 'null' => false, 'default' => NULL),
		'name' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'email' => array('type' => 'string', 'null' => false, 'default' => NULL),
		'sex' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'address' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'town' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'postalcode' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 50),
		'phone' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 100),
		'birthdate' => array('type' => 'date', 'null' => true, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_unicode_ci', 'engine' => 'MyISAM')
	);
	var $newsletter_sended = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'newsletter_id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'index'),
		'email_id' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'sendlist_id' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'email' => array('type' => 'string', 'null' => false, 'default' => NULL, 'key' => 'index'),
		'view' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'sending_id' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'status' => array('type' => 'string', 'null' => true, 'default' => NULL),
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
		'html' => array('type' => 'text', 'null' => true, 'default' => NULL),
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
		'created' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
		'modified' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
		'title' => array('type' => 'string', 'null' => false, 'default' => NULL),
		'description' => array('type' => 'text', 'null' => false, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_unicode_ci', 'engine' => 'MyISAM')
	);
	var $newsletter_stats = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'sended_id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'index'),
		'date' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
		'url' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'ip_address' => array('type' => 'string', 'null' => false, 'default' => NULL),
		'user_agent' => array('type' => 'string', 'null' => false, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'sended_id' => array('column' => 'sended_id', 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_unicode_ci', 'engine' => 'MyISAM')
	);
	var $newsletters = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'active' => array('type' => 'boolean', 'null' => false, 'default' => NULL),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
		'modified' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
		'title' => array('type' => 'string', 'null' => false, 'default' => NULL),
		'date' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
		'html' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'template' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'cache_file' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_unicode_ci', 'engine' => 'MyISAM')
	);
}
?>