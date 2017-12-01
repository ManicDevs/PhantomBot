<?php

namespace Listeners;

class Urlinfo extends \Core\ListenerBase
{
	public function process(&$that, &$socket, $data, $input)
	{
		$sender = $that->sender($data);
		$channel = $that->channel($data);
		preg_match_all('@((https?://)?([-\\w]+\\.[-\\w\\.]+)+\\w(:\\d+)?(/([-\\w/_\\.]*(\\?\\S+)?)?)*)@', $data, $matches);
		foreach($matches[0] as $match)
		{
			$urlData = $this->fetch($match);
			$urlTitle = $this->getUrlTitle($urlData);
			$this->privmsg($socket, $channel, "[URLInfo] Title: {$urlTitle}");
		}
	}
	
	public function getKeywords()
	{
		return array("PRIVMSG");
	}
	
	private function getUrlTitle($data)
	{
		preg_match('#(\<title.*?\>)(\n*\r*.+\n*\r*)(\<\/title.*?\>)#', $data, $matches);
		if(isset($matches[2]))
			return $matches[2];
		
		return false;
	}
}