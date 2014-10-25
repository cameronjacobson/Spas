<?php

namespace Spas;

interface SessionHandlerInterface
{
	public function __construct(array $options);
	public function close();
	public function destroy($session_id);
	public function gc($maxlifetime);
	public function open($save_path, $name);
	public function read($session_id);
	public function write($session_id, array $session_data);
}
