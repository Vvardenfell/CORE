<?php

class Module {
	public $contentPanel = 'GUI_Panel';
	
	protected $mainPanel = 'GUI_Panel_Main';
	
	private $name = '';
	private $routeName = '';
	private $jsRouteReferences = array();
	private $cssRouteReferences = array();
	private $metaTags = array();
	private $submodules = array();
	private $parent = null;
	
	public function __construct($name) {
		if ($name != Text::toLowerCase($name))
			throw new Core_Exception('Use lowercase module names.');
			
		$this->name = $name;
		$this->routeName = $name;
		$this->onConstruct();
	}
	
	// CUSTOM METHODS ----------------------------------------------------------
	public function init() {
		$this->contentPanel = new $this->contentPanel($this->name.'_content');
		$this->mainPanel = new $this->mainPanel('main', $this);
		$this->mainPanel->addClasses($this->name.'_main');
	}
	
	public function addSubmodule(Module $submodule) {
		$this->submodules[$submodule->getRouteName()] = $submodule;
		$submodule->setParent($this);
	}
	
	public function getSubmodule($moduleRouteName) {
		if (isset($this->submodules[$moduleRouteName]))
			return $this->submodules[$moduleRouteName];
		else
			return null;
	}
	
	public function getSubmoduleByName($moduleName) {
		foreach ($this->submodules as $submodule)
			if ($submodule->getName() == $moduleName)
				return $submodule;
		return null;
	}
	
	public function display() {
		$this->mainPanel->render();
	}
	
	/**
	 * Adds a reference to a .js file
	 * @param $routeName the name of a static route, as e.g. defined in routes.php
	 * @param $path the name of your .js file
	 */
	public function addJsRouteReference($routeName, $path) {
		$this->jsRouteReferences[] = Router::get()->getStaticRoute($routeName, $path);
	}
	
	public function getJsRouteReferences() {
		return $this->jsRouteReferences;
	}
	
	/**
	 * Adds a reference to a .css file
	 * @param $routeName the name of a static route, as e.g. defined in routes.php
	 * @param $path the name of your .css file
	 */
	public function addCssRouteReference($routeName, $path) {
		$this->cssRouteReferences[] = Router::get()->getStaticRoute($routeName, $path);
	}
	
	public function getCssRouteReferences() {
		return $this->cssRouteReferences;
	}
	
	/**
	 * @return the route to this module.
	 */
	public function getRoute() {
		$route = $this->getRouteName();
		$module = $this;
		
		while ($module = $module->getParent()) {
			$route = $module->getRouteName().'/'.$route;
		}
		
		if (count(Language_Scriptlet::get()->getAvailableLanguages()) > 1)
			$route = Language_Scriptlet::get()->getCurrentLanguage().'/'.$route;
		
		return PROJECT_ROOTURI.'/'.$route;
	}
	
	/**
	 * Called as soon as the module is constructed.
	 * Override this callback if you want to add additional functionality to the
	 * constructor, without having to override it (-> you don't need to copy all
	 * the parameters).
	 */
	public function onConstruct() {
		// callback
	}
	
	// GETTERS / SETTERS -------------------------------------------------------
	public function getRouteName() {
		return $this->routeName;
	}
	
	public function setRouteName($routeName) {
		$this->routeName = $routeName;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function getParent() {
		return $this->parent;
	}
	
	public function setParent(Module $parentModule) {
		$this->parent = $parentModule;
	}
	
	public function getMetaTags() {
		return $this->metaTags;
	}
	
	public function setMetaTag($key, $value) {
		$this->metaTags[$key] = $value;
	}
}

?>