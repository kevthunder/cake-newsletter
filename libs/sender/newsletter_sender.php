<?php
class NewsletterSender extends Object {
	
	var $controller = null;
	var $defOpt = array();
	var $opt = array();
	
	function init(&$controller,$opt=array()){
		$this->controller = $controller;
		$this->opt = Set::merge($this->defOpt,$opt);
	}
}