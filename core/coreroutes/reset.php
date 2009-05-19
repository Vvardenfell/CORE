<?php

/**
 * Resets the project.
 */
class CoreRoutes_Reset extends Module {
	public function init() {
		parent::init();
		
		// clear database
		DB_Connection::get()->deleteTables();
		
		// remove migrations log
		$file = new IO_File(Core_MigrationsLoader::MIGRATION_LOG_FILE);
		$file->delete();
		
		// clear global cache
		$GLOBALS['cache']->clearAll();
		
		// clear session
		unset($_SESSION);
		
		Core_MigrationsLoader::load();
		
		if (defined('CALLBACK_AFTERRESET'))
			call_user_func(CALLBACK_AFTERRESET);
		
		Scriptlet::redirect(PROJECT_ROOTURI);
	}
}

?>