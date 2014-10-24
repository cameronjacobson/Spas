<?php

namespace Spas;

use \Spas\SessionHandlerInterface;
use \Symfony\Component\HttpFoundation\Session\SessionInterface;
use \Symfony\Component\HttpFoundation\Session\SessionBagInterface;
use \Symfony\Component\HttpFoundation\Session\Storage\MetadataBag;

class Session implements SessionInterface
{
	private $session_id;
	private $session_data;
	protected $bags = array();

	public function __construct(SessionHandlerInterface $handler){
		$this->handler = $handler;
		$this->started = false;
		$this->setName($handler->options['name']);
		$this->session_data = array();
		$this->setMetadataBag();
	}

	public function start(){
		if($this->started){
			return $this->session_id;
		}
		$this->started = true;
		if($session_id = $this->getId()){
			$this->session_data = $this->handler->read($session_id);
		}
		else{
			$this->generateSessionId();
		}
		return $this->getId();
	}

	public function save(){
		$this->handler->write($this->session_id, $this->session_data);
	}

	public function clear(){
		$this->session_data = array();
	}

	public function has($name){
		return isset($name);
	}

	public function get($name, $default = null){
		return empty($this->session_data[$name]) ? $default : $this->session_data[$name];
	}

	public function replace(array $data){
		$this->session_data = $data;
	}

	public function all(){
		return $this->session_data;
	}

	public function remove($name){
		if($this->has($name)){
			unset($this->session_data[$name]);
		}
	}

	public function set($name, $value){
		$this->session_data[$name] = $value;
	}

	public function count(){
		return count($this->session_data);
	}

	public function getId(){
		return $this->session_id;
	}

	public function setId($session_id){
		$this->session_id = $session_id;
	}

	public function getName(){
		if(empty($this->session_name)){
			$this->session_name = session_name();
			$this->setName($this->session_name);
		}
		return $this->session_name;
	}

	public function setName($session_name){
		$this->session_name = $session_name;
	}

	public function isStarted(){
		return $this->started;
	}

	public function invalidate($lifetime = null){
		$this->clear();
		return $this->migrate(true, $lifetime);
	}

	private function generateSessionId(){
		$d = unpack('H*', openssl_random_pseudo_bytes (20, $strong));
		if(empty($strong)){
			error_log('Warning: Your operating system is NOT using a cryptographically secure algorithm to generate session ids');
		}
		$id = base_convert($d[1],16,36);
		$this->setId($id);
		return $id;
	}


	// NOTE:  Not using BAGS right now


	public function migrate($destroy = false, $lifetime = null){
		if ($destroy) {
			$this->metadataBag->stampNew();
		}
		return $this->generateSessionId();
	}

	public function registerBag(SessionBagInterface $bag){
		$this->bags[$bag->getName()] = $bag;
	}

	public function getBag($name){
		if (!isset($this->bags[$name])) {
			throw new \InvalidArgumentException(sprintf('The SessionBagInterface %s is not registered.', $name));
		}

		return $this->bags[$name];
	}

	public function getMetadataBag(){
		if (null === $this->metadataBag){
			$this->metadataBag = new MetadataBag();
		}
		return $this->metadataBag;
	}

	public function setMetadataBag(MetadataBag $metaBag = null){   
		if (null === $metaBag) {
			$metaBag = new MetadataBag();
		}
		$this->metadataBag = $metaBag;
	}

}

