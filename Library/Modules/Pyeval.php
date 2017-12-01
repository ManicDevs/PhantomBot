<?php 

namespace Modules;

class Pyeval extends \Core\ModuleBase
{
	public $helpline = 'evals Python code from given input!';
	
	public $minAcl = 1;
	
	public function process(&$that, &$socket, $data, $input, $command, $args)
	{
		$sender = $that->sender($data);
		$channel = $that->channel($data);
		
		$input = explode(' ', $that->input($data));
		unset($input[0]);
		$input = implode(' ', $input);
		
		$input = stripslashes($input);
		$input = stripcslashes($input);
		
		$opts = array('http' =>
		  array(
			'method'  => 'GET',
			'timeout' => 5
		  )
		);   
		$context  = stream_context_create($opts);
		
		$data = file_get_contents('http://eval.appspot.com/eval?statement=' . urlencode($input), 
			NULL, $context, -1, 512);
		
		$lines = explode("\n", $data);
		$lines = array_splice($lines, 0, count($lines)-1);
		foreach($lines as $linecnt => $line)
		{
			if($linecnt == 10)
				break;
			$this->privmsg($socket, $channel, '[PY] ' . (strlen($data)?$line:'There was no output from your code!'));
		}
	}
}
