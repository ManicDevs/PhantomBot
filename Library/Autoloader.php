<?php 

spl_autoload_register('Autoloader::load');
new Core\ErrorHandler();

class Autoloader
{
	public static function load($className)
	{
		$namespace = str_replace('\\', '/', __NAMESPACE__);
		$className = str_replace('\\', '/', $className);
		$classPath = 'Library/' . (!empty($namespace) ? $namespace : '') . $className . '.class.php';
		
		if(!file_exists($classPath))
		{
			echo '[WARN] Missing: ', $classPath . PHP_EOL;
			exit;
		}
		
		echo '[INFO] Loading: ' . $classPath . PHP_EOL;
		require $classPath;
	}
}
