<?php

class I18N {
  private static $instance = null;
  
  /** array of translations */
  private $translations=array();
  
  /** yaml object */
  private $yamlObject;
  
  private function __construct() {
    // Singleton
    $this->yamlObject = new I18N_Yaml(); 
    $this->translations = $this->yamlObject->loadFilesFromFolder(dirname(__FILE__));
  }
  
  private function getNamespaceParts($path) {
  	return explode('/', $path);
  }
  
  /** */
  public static function translate($key, $language=null) {
  	$instance = self::get();
  	$language = ($language)?$language:Language_Scriptlet::get()->getCurrentLanguage();
  	
  	if(!Language_Scriptlet::get()->isLanguageIdentifier($language))
  	  throw new Core_Exception('Language to be translated to is not a valid one.');
  	
  	$parts=$instance->getNamespaceParts($key);
  	$projectName=mb_strtolower(PROJECT_NAME);
  	
  	/** browse translations array */
  	$translationFound=true;
  	$currentNamespace=false;
  	foreach($parts as $part) {
  		if(!$currentNamespace) {
  			if(array_key_exists($part, $instance->translations))
  		    $currentNamespace=$instance->translations[$part];
  		  else
          $translationFound=false;
  		}
  		else {
        if(array_key_exists($part, $currentNamespace))
          $currentNamespace=$currentNamespace[$part];
        else
          $translationFound=false;
  		}
  	}
  	
  	/** translate last object in current language or given language */
    if($translationFound && array_key_exists($language, $currentNamespace))
      return $currentNamespace[$language];
    else
      $translationFound=false;
      
    if(!$translationFound)
      throw new Core_Exception('Missing translation key for: \''.$key.'\' language: '.$language);
  }
  
  // to load files from framework/project folder
  public function loadFilesFromFolder($folder, $prefix=null) {
  	if($prefix)
     $this->translations[mb_strtolower($prefix)]=$this->yamlObject->loadFilesFromFolder($folder);
  	else
  	 $this->translations[]=$this->yamlObject->loadFilesFromFolder($folder);
  }
  
  public static function get() {
    return (self::$instance) ? self::$instance : self::$instance = new self();
  }
}

?>