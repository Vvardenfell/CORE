<?php

/**
 * Base class for all controls (elements that interact with the user)
 */
abstract class GUI_Control extends GUI_Panel {
	protected $value;
	private $focused = false;
	
	/** contains all validators of this control */
	private $validators = array();
	
	// CONSTRUCTORS ------------------------------------------------------------
	public function __construct($name, $defaultValue = null, $title = '') {
		parent::__construct($name, $title);
		$this->value = $defaultValue;
	}
	
	// CUSTOM METHODS ----------------------------------------------------------
	public function hasValidator($validatorClassName) {
		foreach ($this->validators as $validator)
			if ($validator instanceof $validatorClassName)
				return true;
		return false;
	}
	
	// OVERRIDES ---------------------------------------------------------------
	public function display() {
		parent::display();
		
		if ($this->focused)
			Router::get()->getCurrentModule()->addJsAfterContent(sprintf('$("#%s").focus();', $this->getID()));
	}
	
	public function __toString() {
		return $this->getValue();
	}
	
	protected function validate() {
		foreach ($this->validators as $validator) {
			if (!$validator->isValid()) {
				$this->errors[] = $validator->getError();
				break;
			}
		}
		
		parent::validate();
		
		return $this->errors;
	}
	
	protected function getJsValidators() {
		$validators = array();
		$messages = array();
		foreach ($this->validators as $validator) {
			if ($jsCode = $validator->getJs()) {
				$validators[] = $jsCode[0].': '.$jsCode[1];
				$messages[] = $jsCode[0].': "'.$validator->getError().'"';
			}
		}
		
		$validatorsString = '';
		if (!empty($validators)) {
			$validators[] = sprintf('messages: {%s}', implode(', ', $messages));
			$validatorsString = sprintf('$("#%s").rules("add", {%s});', $this->getID(), implode(', ', $validators));
		}
		
		$validatorsString .= parent::getJsValidators();
		
		return $validatorsString;
	}
	
	protected function generateID() {
		parent::generateID();
		
		if (isset($_POST[$this->getID()]))
			$this->value = $_POST[$this->getID()];
	}
	
	/**
	 * Sets the focus on this control.
	 */
	public function setFocus() {
		$this->focused = true;
	}
	
	// GETTERS / SETTERS -------------------------------------------------------
	public function getValue() {
		return $this->value;
	}
	
	public function setValue($value) {
		$this->value = $value;
	}
	
	public function addValidator(GUI_Validator $validator) {
		$validator->setControl($this);
		$this->validators[] = $validator;
	}
}

?>