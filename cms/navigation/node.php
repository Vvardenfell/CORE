<?php

class CMS_Navigation_Node {
	private $title = '';
	private $cssClasses = array();
	private $module = null;
	private $nodes = array();
	
	public function __construct(Module $module, $title, $cssClasses = array()) {
		$this->title = $title;
		$this->module = $module;
		$this->cssClasses = $cssClasses;
	}
	
	public function render() {
		$result = $this->getLink()->render();
		if ($this->nodes) {
			$result .= '<ul>';
			$i = 0;
			$nodeCount = count($this->nodes);
			foreach ($this->nodes as $node) {
				$classes = $this->getCssClasses();
				$classes[] = 'core_navigation_node';
				if ($nodeCount > 1 && $i == 0)
					$classes[] = 'core_navigation_node_first';
				if ($nodeCount > 1 && $i == $nodeCount - 1)
					$classes[] = 'core_navigation_node_last';
				if ($nodeCount == 1)
					$classes[] = 'core_navigation_node_single';
				if ($node->isActive($this->getModule())) {
					$classes[] = 'core_navigation_node_active';
					$classes[] = 'core_navigation_node_inpath';
				}
				elseif ($node->isInPath($this->getModule())) {
					$classes[] = 'core_navigation_node_inpath';
				}
				$result .= '<li class="'.implode(' ', $classes).'">';
				$result .= $node->render();
				$result .= '</li>';
				$i++;
			}
			$result .= '</ul>';
		}
		return $result;
	}
	
	public function getLink() {
		return new GUI_Control_Link('core_navigation_node_link', $this->getTitle(), $this->module->getUrl());
	}
	
	public function isInPath(Module $module) {
		foreach ($module->getAllSubmodules() as $subModule) {
			if ($this->isActive($subModule))
				return true;
			else
				return $this->isInPath($subModule);
		}
		
		return false;
	}
	
	public function isActive(Module $module) {
		return (Router::get()->getCurrentModule() == $module);
	}
	
	/**
	 * Adds a new navigation node for a given module.
	 * @param $nodeTitle text to display for the module
	 * @param $module
	 * @return CMS_Navigation_Node the newly added navigation node
	 */
	public function addModuleNode($nodeTitle, Module $module) {
		$node = new CMS_Navigation_Node($nodeTitle, $module);
		$this->nodes[] = $node;
		return $node;
	}
	
	// GETTERS / SETTERS -------------------------------------------------------
	public function getTitle() {
		return $this->title;
	}
	
	public function setTitle($title) {
		$this->title = $title;
	}
	
	public function getCssClasses() {
		return $this->cssClasses;
	}
	
	/**
	 * @return Module
	 */
	public function getModule() {
		return $this->module;
	}
	
	public function setModule(Module $module) {
		$this->module = $module;
	}
}

?>