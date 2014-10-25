<?php

namespace Spas;

class FileSessionHandler implements SessionHandlerInterface
{
	// IMPORTANT:  BASEPATH - For now this serves to help protect
	//   people from themselves
	const BASEPATH = '/tmp';

	private $savePath;
	public $options;

	public function __construct(array $options){
		$this->savePath = $options['save_path'];
		$this->options = $options;
		$this->session_dir = $session_dir = self::BASEPATH.$this->savePath;
		if(!file_exists($session_dir)){
			if(!mkdir($session_dir, 0777, true)){
				die('Unable to create sessions directory'.PHP_EOL);
			}
		}
		else{
			if(!is_dir($session_dir)){
				die('Session save_path is not a directory'.PHP_EOL);
			}
		}
	}

	public function close(){
		return true;
	}

	public function destroy($session_id){
		$file = $this->session_dir.'/sess_'.$session_id;
		if(file_exists($file)){
			unlink($file);
		}
		return true;
	}

	public function gc($maxlifetime = 0) {
		foreach (glob($this->session_dir."/sess_*") as $file) {
			if (filemtime($file) + $maxlifetime < time() && file_exists($file)) {
				$tmp = explode('/sess_',$file);
				$tmp = array_splice($tmp,-1);
				$this->destroy($tmp[0]);
			}
		}
		return true;
	}

	public function open($save_path, $name){
		return true;
	}

	public function read($session_id){
		$file = $this->session_dir.'/sess_'.$session_id;
		if(!file_exists($file)){
			return array();
		}
		$session_data = (string)@file_get_contents($file);
		$session_data = unserialize($session_data);
		return empty($session_data) ? array() : $session_data;
	}

	public function write($session_id, array $session_data){
		$session_data = serialize($session_data);
		return file_put_contents($this->session_dir.'/sess_'.$session_id,$session_data);
	}
}
