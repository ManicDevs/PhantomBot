<?php

namespace Modules;

class Join extends \Core\ModuleBase
{
	public $helpline = 'joins the bot to a channel.';
	
	public $minAcl = 7;	

	public function process(&$that, &$socket, $data, $input, $command, $args)
	{
		$sender = $that->sender($data);
		$channel = $that->channel($data);
		if($that->getLevel($sender, '', $that->host($data)) > 6)
		{
			$this->send($socket, 'JOIN ' . $args);
		}
		else
		{
			$this->privmsg($socket, $channel, "{$sender}: You are not authorized to do that.");
		}
	}
}