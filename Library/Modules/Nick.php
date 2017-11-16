<?php

namespace Modules;

class Nick extends \Core\ModuleBase
{
	public $helpline = 'changes the bots nickname.';
	
	public $minAcl = 8;	

	public function process(&$that, &$socket, $data, $input, $command, $args)
	{
		$sender = $that->sender($data);
		$channel = $that->channel($data);
		if($that->getLevel($sender, '', $that->host($data)) > 7)
		{
			$this->send($socket, 'NICK ' . $args);
		}
		else
		{
			$this->privmsg($socket, $channel, "{$sender}: You are not authorized to do that.");
		}
	}
}