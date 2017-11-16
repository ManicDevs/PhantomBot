<?php 

chdir(__DIR__);
set_time_limit(0);
error_reporting(0);

require 'Library/Autoloader.php';

if(!file_exists('Config/Configuration.php'))
{
	echo '[WARN] Configuration missing, please see dir:Config/' . PHP_EOL;
	exit;
}
$config = require 'Config/Configuration.php';

$shmop = shmop_open(0xff4, "c", 0644, 1);
shmop_write($shmop, '0', 0);

$bot = new Core\PhantomCore($shmop, $config);

$bot->load(true);

//declare(ticks=1);

pcntl_signal(SIGINT, function()
{
	global $bot;
    if($bot->isConnected())
    	$bot->disconnect();
    exit;
});

while(true)
{
	if($data = $bot->listen())
	{
		$bot->process($data);
	}
	pcntl_signal_dispatch();
}

$bot->disconnect();
