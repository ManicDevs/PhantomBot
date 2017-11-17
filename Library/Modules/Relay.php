<?php

namespace Modules;

class Relay extends \Core\ModuleBase
{
	public $helpline = 'relays an irc network channel to another.';
	
	public $minAcl = 7;	
	
	private $socket = null;
	private $channel = null;
	private $sender = null;
	
	private $pids = array();
	
	public function __destruct()
	{
		foreach($this->pids as $network => $pid)
		{
			shell_exec("kill -9 $pid");
		}
	}
	
	public function process(&$that, &$socket, $data, $input, $command, $args)
	{
		$sender = $that->sender($data);
		$channel = $that->channel($data);
		$input = explode(' ', $that->input($data));
		if($that->getLevel($sender, '', $that->host($data)) > 6)
		{
			switch(@$input[1])
			{
				case 'list':
					if(count($this->pids) == 0)
					{
						$this->privmsg($socket, $channel, "{$sender}, There are no active relay threads!");
						return;
					}
					
					$this->send($socket, "NOTICE {$sender} :Active relay threads¬");
					$cnt = 1;
					foreach($this->pids as $network => $pid)
					{
						$this->send($socket, "NOTICE {$sender} :[$cnt] $pid => $network");
						$cnt++;
					}
					return;
				break;
				
				case 'kill':
					if($input[2] === '*')
					{
						if(count($this->pids) == 0)
						{
							$this->privmsg($socket, $channel, "{$sender}, There are no active relay threads!");
							return;
						}
						
						$pids = "";
						foreach($this->pids as $network => $pid)
						{
							$pids += "$pid, ";
							shell_exec("kill -9 $pid");
							unset($this->pids[$network]);
						}
						
						$this->privmsg($socket, $channel, "{$sender}, Killed relay thread PID(s): $pids.");
						return;
					}
					
					foreach($this->pids as $network => $pid)
					{
						if(isset($this->pids[$network]) && $this->pids[$network] === $input[2])
						{
							$this->privmsg($socket, $channel, "{$sender}, Killing relay thread PID: $pid.");
							shell_exec("kill -9 $pid");
							unset($this->pids[$network]);
						}
					}
					$this->privmsg($socket, $channel, "{$sender}, That PID doesn't exist!");
					return;
				break;
				
				case 'connect':
					$pid = pcntl_fork();
					$this->pids["{$input[6]}!{$input[4]}@{$input[2]}:{$input[3]}"] = $pid;
					if(!$pid)
					{	
						$relayhere = false;
						if(strcasecmp($input[5], 'true') == 0 || $input[5] === '1')
						{
							$relayhere = true;
						}
						
						$showuserhost = false;
						if(strcasecmp($input[8], 'true') == 0 || $input[8] === '1')
						{
							$showuserhost = true;
						}
						
						$this->socket = $socket;
						$this->channel = $channel;
						$this->sender = $sender;
						
						$this->IRCConnect($input[2], $input[3], $input[4], $relayhere, $input[6], $input[7], $showuserhost);
					}
				break;
				
				default:
					$this->privmsg($socket, $channel, "{$sender}, Arguments list: list | kill [*|pidof] | connect [host port channel relayhere[true|false] nickname ident showuserhost[true|false]]");
				break;
			}
		}
		else
		{
			$this->privmsg($socket, $channel, "{$sender}: You are not authorized to do that.");
		}
	}
	
	private function IRCConnect($host, $port, $channel, $relayhere, $nickname, $ident, $showsenderhost)
	{
		if(substr($port, 0, 1) === '+')
		{
			$context = stream_context_create(['ssl' => [
                    'verify_peer'      => false,
                    'verify_peer_name' => false,
                    'allow_self_signed'=> true
			]]);
			$port = intval(substr($port, 1));
			$socket = stream_socket_client("ssl://$host:$port", $errno, $errstr, 5, STREAM_CLIENT_CONNECT, $context);
		}
		else
		{
			$port = intval($port);
			$socket = stream_socket_client("tcp://$host:$port", $errno, $errstr, 5, STREAM_CLIENT_CONNECT);
		}
		
		if($socket == FALSE)
		{
			$this->privmsg($this->socket, $this->channel, "{$this->sender}, Failed to relay IRC [$nickname!$channel@$host:$port]");
			return;
		}
		
		$this->privmsg($this->socket, $this->channel, "{$this->sender}, Relaying IRC [$nickname!$channel@$host:$port]");
		
		if($relayhere)
		{
			$this->privmsg($this->socket, $this->channel, "{$this->sender}, Relaying message from [$nickname!$channel@$host:$port] to this channel!");
		}
		else
		{
			$this->send($this->socket, "JOIN $channel");
			$this->privmsg($this->socket, $this->channel, "{$this->sender}, Join channel $channel on this network to see relayed messages from [$nickname!$channel@$host:$port]");	
		}
		
		fputs($socket,"USER $ident 8 * :$ident\r\n");
		fputs($socket,"NICK $nickname\r\n");
		
		while(true)
		{
			while($data = substr_replace(fgets($socket), '', -2))
			{	
				$splitdata = explode(' ', $data);
				
				if(strcasecmp($splitdata[0], 'PING') == 0)
					fputs($socket, "PONG " . $splitdata[1] . "\r\n");
					
				if(count($splitdata) < 4)
					continue;
				
				$sender = $splitdata[0];
				$action = $splitdata[1];
				$recipient = $splitdata[2];
				
				if(strcasecmp($action, '422') == 0 || strcasecmp($action, '376') == 0)
				{
					fputs($socket, "JOIN $channel\r\n");
				}
				elseif(strcasecmp($action, 'PRIVMSG') == 0)
				{
					$message = substr($data, strpos($data, ' :') + 2);
					
					if(isset($message) && strlen($message) > 0)
					{
						if(!$showsenderhost)
						{
							$sender = explode('!', $sender);
							$sender = $sender[0];
						}
						
						if($relayhere)
						{
							$this->privmsg($this->socket, $this->channel, "[$recipient@$host]<" . substr($sender, 1) . "> $message");
						}
						else
						{
							$this->privmsg($this->socket, $channel, "[$recipient@$host]<".substr($sender, 1)."> $message");
						}
					}
				}
			}
		}
		fclose($socket);
	}
}