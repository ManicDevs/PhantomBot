<?php

namespace Core;

class ErrorHandler
{
	private $old_handler = null;
	
	public function __construct()
	{
		$this->old_handler = set_error_handler(array($this, 'runtime_handler'), ~E_NOTICE & ~E_USER_NOTICE);
		register_shutdown_function(array($this, 'shutdown_handler'));
	}
	
	public function runtime_handler($errno, $errstr, $errfile, $errline)
	{
		if(!(error_reporting() & $errno))
		{
			// This error code is not included in error_reporting, so let it fall
			// through to the standard PHP error handler.
			return false;
		}
		
		switch($errno)
		{
			case E_USER_ERROR:
				printf("[ERROR] Type: [%s] %s\n" .
					"  Fatal error on line %s in file %s" .
					", PHP %s (%s)\nAborting...\n",
					$errno, $errstr, $errline, $errfile,
					PHP_VERSION, PHP_OS);
				exit;
			break;
			
			case E_USER_WARNING:
				printf("[WARNING] Type: [%s] %s\n",
					$errno, $errstr);
			break;
			
			case E_USER_NOTICE:
				printf("[NOTICE] Type: [%s] %s\n",
					$errno, $errstr);
			break;
			
			default:
				if(!empty($type))
					printf("[Unknown] Type: [%s] %s\n",
						$errno, $errstr);
			break;
		}
		
		return true;
	}
	
	public function shutdown_handler()
	{
		$err = error_get_last();
		
		switch($err['type'])
		{
			case E_ERROR:
				printf("[FATAL] Type: [%s] %s\n",
					$err['type'], $err['message']);
			break;
			
			default:
				if(!empty($type))
					printf("[Unknown] Type: [%s] %s\n",
						$err['type'], $err['message']);
			break;
		}
	}
}