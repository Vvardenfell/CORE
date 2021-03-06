<?php

/**
 * @package CORE PHP Framework
 * @copyright Copyright (C) 2012 Sebastian Mayer, Andreas Sicking, Andre Jährling
 * @license GNU/GPL, see license.txt
 * This file is part of CORE PHP Framework.
 *
 * CORE PHP Framework is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * CORE PHP Framework is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with CORE PHP Framework. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * A file input field
 */
class GUI_Control_FileUpload extends GUI_Control {
	// Images
	const TYPE_JPEG = 'image/jpeg';
	const TYPE_GIF = 'image/gif';
	const TYPE_PNG = 'image/png';
	// Textfiles
	const TYPE_TXT = 'text/plain';
	const TYPE_PDF = 'application/pdf';
	const TYPE_HTML = 'text/html';
	const TYPE_WORD = 'application/msword';
	const TYPE_ODT = 'application/vnd.oasis.opendocument.text';
	// Archives
	const TYPE_ZIP = 'application/zip';
	const TYPE_RAR = 'application/x-rar-compressed';
	// ... add more, see http://de.php.net/manual/en/function.mime-content-type.php#87856
	
	// Misc
	public static $uploadDirectory = null;
	private $maxFileSize = 0;
	private $allowedFiletypes = array();
	
	// CONSTRUCTORS ------------------------------------------------------------
	/**
	 * @param $maxFileSize maximum allowed file size in bytes
	 */
	public function __construct($name, $maxFileSize, $title = '') {
		parent::__construct($name, null, $title);

		$this->setTemplate(dirname(__FILE__).'/fileupload.tpl');
		$this->setMaxFilesize($maxFileSize);
	}
	
	// CUSTOM METHODS ----------------------------------------------------------
	/**
	 * Move file after upload
	 * @param $path Save place for uploaded file
	 * @return array name: original name set by user; new_name: name for file in filesystem; path: full path to uploaded file
	 */
	public function moveTo($path) {
		if (!$this->value['name'])
			return false;
		
		$pathparts = explode(DIRECTORY_SEPARATOR, str_replace(array('\\', '/'), DIRECTORY_SEPARATOR, $path));
		$path = implode(DIRECTORY_SEPARATOR, array_map('urlencode', $pathparts));
		
		if (!is_dir(self::getUploadDirectory().DIRECTORY_SEPARATOR.$path)) {
			mkdir(self::getUploadDirectory().DIRECTORY_SEPARATOR.$path, 0777, true);
			$htaccess = new IO_File(self::getUploadDirectory().DIRECTORY_SEPARATOR.'.htaccess');
			if (!$htaccess->exists()) {
				$htaccess->open(IO_File::WRITE_NEWFILE);
				$htaccess->write('authType basic'.System::getNewLine().'order deny,allow'.System::getNewLine().'deny from all');
				$htaccess->close();
			}
		}
			
		$filename = time().'.'.IO_Utils::getFileExtension($this->value['name']);
		move_uploaded_file($this->value['tmp_name'], self::getUploadDirectory().DIRECTORY_SEPARATOR.$path.DIRECTORY_SEPARATOR.$filename);
		return array('name' => $this->value['name'], 'new_name' => $filename, 'path' => self::getUploadDirectory().DIRECTORY_SEPARATOR.$path);
	}
	
	// OVERRIDES / IMPLEMENTS --------------------------------------------------
	protected function generateID() {
		parent::generateID();
		
		if (isset($_FILES[$this->getID()]))
			$this->value = $_FILES[$this->getID()];
	}
	
	protected function validate() {
		parent::validate();
		
		if ($this->value['error'] != UPLOAD_ERR_NO_FILE) {
			if ($this->value['error'] != UPLOAD_ERR_OK) {
				switch ($this->value['error']) {
					case UPLOAD_ERR_INI_SIZE:
						$this->addError('The uploaded file exceeds the upload_max_filesize directive in php.ini');
					break;
					case UPLOAD_ERR_FORM_SIZE:
						$this->addError('The uploaded file exceeds the MAX_FILE_SIZE ('.round($this->getMaxFilesize() / 1024, 2).' KB) directive that was specified in the HTML form');
					break;
					case UPLOAD_ERR_PARTIAL:
						$this->addError('The uploaded file was only partially uploaded');
					break;
					case UPLOAD_ERR_NO_FILE:
						/* do not display this message, confuses user when formular contains other elements
						 * and he didn't want to upload a file
						$this->error('No file was uploaded');
		 				*/
					break;
					case UPLOAD_ERR_NO_TMP_DIR:
						$this->addError('Missing a temporary folder.');
					break;
					case UPLOAD_ERR_CANT_WRITE:
						$this->addError('Failed to write file to disk');
					break;
					case UPLOAD_ERR_EXTENSION:
						$this->addError('File upload stopped by extension');
					break;
				}
			}
			if (!$this->hasErrors()) {
				if (!is_uploaded_file($this->value['tmp_name'])) {
					$this->addError('Possible file upload attack: '.$this->value['tmp_name']);
				}
				else if ($this->value['size'] > $this->getMaxFilesize()) {
					$this->addError('Filesize is greater than ('.round($this->getMaxFilesize() / 1024, 2).' KB)');
				}
				else if (!in_array($this->value['type'], $this->allowedFiletypes)) {
					$this->addError('Filetype is not allowed here: '.$this->value['type']);
				}
			}
		}
		return $this->errors;
	}
	
	// GETTERS / SETTERS -------------------------------------------------------
	/**
	 * Maximal filesize in Bytes.
	 */
	public function setMaxFilesize($size) {
		$this->maxFileSize = (int)$size;
	}
	
	public function getMaxFilesize() {
		return $this->maxFileSize;
	}
	
	/**
	 * Set the allowed Filetypes for this upload. Use self::TYPE_*
	 */
	public function setAllowedFiletypes(array $types) {
		$filetypes = $this->getFileTypes();
		foreach ($types as $type)
			if (in_array($type, $filetypes))
				$this->allowedFiletypes[] = $type;
	}
	
	private function getFileTypes() {
		$reflection = new ReflectionClass(__CLASS__);
		$constants = $reflection->getConstants();
		$types = array();
		foreach ($constants as $key => $constant)
			if (strpos($key, 'TYPE_') === 0)
				$types[] = $constant;
		return $types;
	}
	
	public static function getUploadDirectory() {
		if (!self::$uploadDirectory)
			self::$uploadDirectory = PROJECT_PATH.DIRECTORY_SEPARATOR.'www'.DIRECTORY_SEPARATOR.'uploads';
			
		return self::$uploadDirectory;
	}
}

?>