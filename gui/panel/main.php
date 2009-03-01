<?php

class GUI_Panel_Main extends GUI_Panel {
	private $module = null;
	
	public function __construct($name, Module $module) {
		parent::__construct($name);
		
		$this->module = $module;
	}
	
	public function displayPage() {
		$this->module->contentPanel->display();
	}
	
	public function render() {
		require dirname(__FILE__).'/main.tpl';
	}
}

?>