<?php

namespace Modules;

class Ignore extends \Core\ModuleBase
{
	public $helpline = 'ignores an abusive user from using commands.';
	
	public $minAcl = 7;	

	public function process(&$that, &$socket, $data, $input, $command, $args)
	{
		$sender = $that->sender($data);
		$channel = $that->channel($data);
		if($that->getLevel($sender, '', $that->host($data)) > 6)
		{
			$input = explode(' ', $that->input($data));
			switch(strtolower(@$input[1]))
			{
				case 'list':
					$this->privmsg($socket, $channel, "TODO: List");
					print_r($that->ignores);
				break;
				
				case 'add':
					$this->privmsg($socket, $channel, "TODO: Add");
					if(isset($input[2]))
					{
						$that->ignores['nicks'][$input[2]] = array();
					}
				break;
				
				case 'del':
					$this->privmsg($socket, $channel, "TODO: Del");
					if(isset($input[2]))
						unset($that->ignores['nicks'][$input[2]]);
				break;
				
				default:
					$this->send($socket, "NOTICE {$sender} :Arguments list | add [nick|host] | del [nick|host]");
				break;
			}
		}
		else
		{
			$this->privmsg($socket, $channel, "{$sender}: You are not authorized to do that.");
		}
	}
}