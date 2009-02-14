<?php

class GUI_Control_Textfield extends GUI_Control {
	// CONSTRUCTORS ------------------------------------------------------------
	public function __construct($name, $defaultValue = null) {
		parent::__construct($name, $defaultValue);
		
		$this->setTemplate(dirname(__FILE__).'/textfield.tpl');
		$this->addClasses('textfield');
	}
}

?>