<?php

/**
 * For handling default routes like core/reset
 * @author Patrick
 */
class CoreRoutes_Core extends Module {
	public function __construct($name) {
		parent::__construct($name);
		
		$this->addSubmodule(new CoreRoutes_Reset('reset'));
	}
	
	public function init() {
		parent::init();
		
		Router::get()->addStaticRoute('core_css', './../../CORE/www/css');
  		Router::get()->addStaticRoute('core_js', './../../CORE/www/js');
	}
}

?>