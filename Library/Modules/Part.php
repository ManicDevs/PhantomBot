<?php

namespace Modules;

class Part extends \Core\ModuleBase
{
	public $helpline = 'changes the bots nickname.';
	
	public $minAcl = 7;	

	public function process(&$that, &$socket, $data, $input, $command, $args)
	{
		$sender = $that->sender($data);
		$channel = $that->channel($data);
		if($that->getLevel($sender, '', $that->host($data)) > 6)
		{
			$this->send($socket, 'PART ' . $args);
		}
		else
		{
			$this->privmsg($socket, $channel, "{$sender}: You are not authorized to do that.");
		}
	}
}