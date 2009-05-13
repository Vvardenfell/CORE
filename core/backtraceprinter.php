<?php

class Core_BacktracePrinter {
	/**
	 * Clears the output buffer and displays an error page.
	 * Per default the error page contains the error message and a backtrace.
	 * On the Live Environment, a callback defined by the constant ERROR_CALLBACK
	 * is called (if defined).
	 * @param $backtrace the backtrace to display
	 * @param $customMessage
	 * @param $errorType a custom error type, useful to categorize errors.
	 */
	public static function printBacktrace(Array $backtrace, $customMessage = '', $errorType = '') {
		if (ob_get_level() > 0)
			ob_end_clean();
			
		foreach ($backtrace as &$backtraceItem) {
			if (isset($backtraceItem['args'])) {
				foreach ($backtraceItem['args'] as &$argument) {
					if (is_object($argument))
						$argument = get_class($argument);
					elseif ($argument === null)
						$argument = 'null';
					else
						$argument = '\''.$argument.'\'';
				}
			}
		}
		
		if (Environment::getCurrentEnvironment() == Environment::LIVE && defined('ERROR_CALLBACK'))
			call_user_func(ERROR_CALLBACK, $backtrace, $customMessage, $errorType);
		else
			require_once('backtraceprinter.tpl');
		exit;
	}
}

?>