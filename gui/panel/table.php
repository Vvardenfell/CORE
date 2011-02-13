<?php

/**
 * Panel to display a html table
 */
class GUI_Panel_Table extends GUI_Panel {
	private $lines = array();
	private $header = array();
	private $footer = array();
	private $numberOfColumns = 0;
	private $enableSortable = false;
	private $sorterOptions = array();
	private $tableCssClasses = array();
	private $foldEvery = 0;
	
	private static $firstTableOnPage = true;
	
	public function __construct($name, $title = '') {
		parent::__construct($name, $title);
		
		$this->setTemplate(dirname(__FILE__).'/table.tpl');
		$this->setAttribute('summary', $title);
		$this->addClasses('core_gui_table');
		if (self::$firstTableOnPage) {
			$this->addJS('
				var foldedAfter = new Array();
				var foldEvery = new Array();
			');
		}
	}
	
	public function afterInit() {
		parent::afterInit();		
		
		if (self::$firstTableOnPage) {
			self::$firstTableOnPage = false;
			if ($this->enabledSortable()) {
				$this->getModule()->addJsRouteReference('core_js', 'jquery/jquery.tablesorter.js');
			
				$this->addJS('
					$.tablesorter.addParser(
						{
							id: "separatedDigit",
							is: function(s) {
								return false;
							},
							format: function(s) {
								return jQuery.tablesorter.formatFloat(s.replace(/\./g, ""));
							},
							type: "numeric"
						}
					);
					$("#'.$this->getID().'").tablesorter(
						{
							'.$this->getSorterOptions().'
						}
					);
				');
				$this->addClasses('core_gui_table_sortable');
			}
		}
		$this->addJS('
			foldedAfter[\''.$this->getName().'\'] = '.$this->getFoldEvery().';
			foldEvery[\''.$this->getName().'\'] = '.$this->getFoldEvery().';
		');
	}
	
	public function displayCell($cell) {
		if ($cell instanceof GUI_Panel)
			$cell->display();
		else
			echo $cell;
	}
	
	// GETTERS / SETTERS -------------------------------------------------------
	
	public function addLine(array $line) {
		if ($this->numberOfColumns == 0)
			$this->numberOfColumns = count($line);
		if (count($line) != $this->numberOfColumns) {
			$this->addError('Die \''.$line[0].'\' Zeile hat zu viele / wenige Spalten und wurde nicht angefügt!');
			return;
		}
		foreach ($line as $column) {
			if ($column instanceof GUI_Panel)
				$this->addPanel($column);
		}
		$this->lines[] = $line;
	}
	
	public function addHeader(array $line) {
		if ($this->numberOfColumns == 0)
			$this->numberOfColumns = count($line);
		if (count($line) != $this->numberOfColumns) {
			$this->addError('Die \''.$line[0].'\' Headerzeile hat zu viele / wenige Spalten und wurde nicht angefügt!');
			return;
		}
		foreach ($line as $column) {
			if ($column instanceof GUI_Panel)
				$this->addPanel($column);
		}
		$this->header[] = $line;
	}
	
	public function addFooter(array $line) {
		if ($this->numberOfColumns == 0)
			$this->numberOfColumns = count($line);
		if (count($line) != $this->numberOfColumns) {
			$this->addError('Die \''.$line[0].'\' Footerzeile hat zu viele / wenige Spalten und wurde nicht angefügt!');
			return;
		}
		foreach ($line as $column) {
			if ($column instanceof GUI_Panel)
				$this->addPanel($column);
		}
		$this->footer[] = $line;
	}
	
	/**
	 * Sets a css class for given rows/columns
	 * To set a class for every n-th column, set $line to null
	 * To set a class for every n-th line, set $column to null
	 * @param string $class
	 * @param int $column
	 * @param int $line
	 */
	public function addTableCssClass($class, $column = null, $line = null) {
		$this->tableCssClasses[$column][$line]['classes'][] = $class;
	}
	
	public function getLines() {
		return $this->foldEvery > 0 ? array_slice($this->lines, 0, $this->getModule()->getParam('fold') > 0 ? $this->getModule()->getParam('fold') + $this->foldEvery : $this->foldEvery, true) : $this->lines;
	}
	
	public function getHeaders() {
		return $this->header;
	}
	
	public function getFooters() {
		return $this->footer;
	}
	
	public function enabledSortable() {
		return $this->enableSortable;
	}
	
	public function enableSortable($enable = true) {
		$this->enableSortable = $enable;
	}
	
	public function addSorterOption($javascript) {
		$this->sorterOptions[] = $javascript;
	}
	
	private function getSorterOptions() {
		return implode(', ', $this->sorterOptions);
	}
	
	public function getColumnCount() {
		return $this->numberOfColumns;
	}
	
	public function getTrAttributeString($row) {
		if (isset($this->tableCssClasses[null][$row]['classes']))
			return 'class="'.implode(' ', $this->tableCssClasses[null][$row]['classes']).'"';
		else
			return '';
	}
	
	public function getTdAttributeString($column, $row) {
		$classes = array();
		if (isset($this->tableCssClasses[$column][null]['classes']))
			$classes = array_merge($classes, $this->tableCssClasses[$column][null]['classes']);
		if (isset($this->tableCssClasses[$column][$row]['classes']))
			$classes = array_merge($classes, $this->tableCssClasses[$column][$row]['classes']);
		if ($classes)
			return 'class="'.implode(' ', $classes).'"';
		else
			return '';
	}
	
	public function setFoldEvery($rows, $caption = 'weiter', $successJsCallback = null) {
		$this->foldEvery = (int)$rows;
		$this->addPanel($link = new GUI_Control_JsLink('foldlink', $caption, ''));
		$module = $this->getModule();
		$link->setUrl($module->getUrl(array_merge($module->getParams(), array('fold' => $module->getParam('fold') > 0 ? $module->getParam('fold') + $this->foldEvery : $this->foldEvery))));
		$link->setJs('
			$.core.ajaxRequest(
				\''.$this->getAjaxID().'\',
				\'ajaxGetFoldedLines\',
				{ after: foldedAfter[\''.$this->getName().'\'], every: foldEvery[\''.$this->getName().'\'] },
				function(data) {
					$(data).insertBefore($(\'#'.$this->getAjaxID().'-fold\'));
					foldedAfter[\''.$this->getName().'\'] += foldEvery[\''.$this->getName().'\'];
					'.($successJsCallback === null ? '' : $successJsCallback).'
				}
			);
			return false;
		');
		$link->setAttribute('id', $this->getAjaxID().'-foldlink');
	}
	
	public function getFoldEvery() {
		return $this->foldEvery;
	}
	
	// AJAX-CALLBACKS ----------------------------------------------------------
	
	public function ajaxGetFoldedLines() {
		$str = '';
		foreach (array_slice($this->lines, $_POST['after'], $_POST['every']) as $line) {
			$str .= '<tr>';
			foreach ($line as $col) {
				$str .= '<td>'.($col instanceof GUI_Panel ? $col->render() : $col).'</td>';
			}
			$str .= '</tr>';
		}
		return $str;
	}
}

?>