<?php

namespace Core;

abstract class ListenerBase
{
	protected $size = 512;
	
	//abstract function process(&$that, &$socket, $data, $input);
	
	//abstract function getKeywords();
	
	protected function send($socket, $signal)
	{
		fputs($socket, Helpers\Str::trim($signal) . PHP_EOL);
		usleep(100000);
		echo '[SEND] ' . $signal . PHP_EOL;
	}
	
	protected function privmsg($socket, $target, $message)
	{
		$this->send($socket, 'PRIVMSG ' . Helpers\Str::trim($target) . ' :' . Helpers\Str::trim($message));
		usleep(100000);
	}
	
	protected function notice($socket, $target, $message)
	{
		$this->send($socket, 'NOTICE ' . Helpers\Str::trim($target) . ' :' . Helpers\Str::trim($message));
		usleep(100000);
	}
	
	protected function getArguments($data)
	{
		$args = explode(' ', $data);
		$func = function($value)
		{
			return Helpers\Str::trim($value);
		};
		
		return array_map($func, $args);
	}
	
	protected function fetch($uri)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $uri);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Listener/Bot');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
		
		$output = curl_exec($ch);
		
		curl_close($ch);
		
		return $output;
	}
}