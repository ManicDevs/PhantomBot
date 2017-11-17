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
					if(!isset($that->ignores['nicks']) || !is_array($that->ignores['nicks']))
					{
						$this->send($socket, "NOTICE {$sender} :There are no ignored nicknames!");
						return;
					}
					
					$cnt = 1;
					foreach(@$that->ignores['nicks'] as $nick => $hosts)
					{
						$hosts = isset($hosts[0])?implode('|', $hosts):'None';
						$this->send($socket, "NOTICE {$sender} :Ignored nicknames¬");
						$this->send($socket, "NOTICE {$sender} :[$cnt] Nick: {$nick} ~ Hosts: " . $hosts);
						$cnt++;
					}
					
					$cnt = 1;
					foreach(@$that->ignores['hosts'] as $host => $nicks)
					{
						$nicks = isset($nicks[0])?implode('|', $nicks):'None';
						$this->send($socket, "NOTICE {$sender} :Ignored hostnames¬");
						$this->send($socket, "NOTICE {$sender} :[$cnt] Host: {$host} ~ Nicks: " . $nicks);
						$cnt++;
					}
				break;
				
				case 'add':
					if(isset($input[2]))
					{
						$this->send($socket, 'WHOIS ' . $input[2]);
						$data = $that->listen();
						$nosuchnick = $that->expect($data, 'nosuchnickchannel', array('who' => $input[2]));
						
						if(!isset($that->ignores['nicks']) && $nosuchnick === false)
						{
							$this->privmsg($socket, $channel, "{$sender}, Adding ignore for nickname: {$input[2]}");
							$that->ignores['nicks'][$input[2]] = array();
							return;
						}
						
						if(!isset($that->ignores['hosts']))
						{
							$this->privmsg($socket, $channel, "{$sender}, Adding ignore for hostname: {$input[2]}");
							$that->ignores['hosts'][$input[2]] = array();
							return;
						}
					}
				break;
				
				case 'del':
					if(isset($input[2]))
					{
						if($input[2] === '*')
						{
							$this->privmsg($socket, $channel, "{$sender}, Clearing ignore lists!");
							unset($that->ignores['nicks']);
							$that->ignores['nicks'] = array();
							return;
						}
						
						if(isset($that->ignores['nicks']))
						{
							$this->privmsg($socket, $channel, "{$sender}, Deleting ignore for nickname: {$input[2]}");
							unset($that->ignores['nicks'][$input[2]]);
							return;
						}
						
						if(isset($that->ignores['hosts']))
						{
							$this->privmsg($socket, $channel, "{$sender}, Deleting ignore for hostname: {$input[2]}");
							unset($that->ignores['hosts'][$input[2]]);
							return;
						}
					}
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
