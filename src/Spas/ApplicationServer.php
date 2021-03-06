<?php

namespace Spas;

use \Spas\SilexApp;
use \Spas\ApplicationInterface;
use \EventBase;
use \EventBuffer;
use \EventUtil;
use \Event;
use \EventListener;
use \EventHttp;
use \EventHttpRequest;
use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;

class ApplicationServer
{
	private $base;
	private $http;
	private $app;
	private $reqHeaders;
	private $addr;
	private $name;
	private $port;

	public static $methods = array(
		1   => 'GET',
		2   => 'POST',
		4   => 'HEAD',
		8   => 'PUT',
		16  => 'DELETE',
		32  => 'OPTIONS',
		64  => 'TRACE',
		128 => 'CONNECT',
		256 => 'PATCH',
	);

	private static $serverDefaults = array(
		'HTTP_USER_AGENT'      => 'Spas/0.1',
		'HTTP_ACCEPT'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
		'HTTP_ACCEPT_LANGUAGE' => 'en-us,en;q=0.5',
		'HTTP_ACCEPT_CHARSET'  => 'ISO-8859-1,utf-8;q=0.7,*;q=0.7',
		'REMOTE_ADDR'          => '127.0.0.1',
		'SCRIPT_NAME'          => '',
		'SCRIPT_FILENAME'      => '',
		'SERVER_PROTOCOL'      => 'HTTP/1.1',
	);

	public function __construct($addr, $port, ApplicationInterface $app){
		if(!function_exists('openssl_random_pseudo_bytes')){
			die(PHP_EOL.'ERROR: System requires openssl_random_pseudo_bytes to generate session ids'.PHP_EOL);
		}

		$this->app = $app;
		$this->base = new EventBase();
		$this->http = new EventHttp($this->base);
		$this->http->setDefaultCallback(array($this,'callback'));

		$this->app['base'] = $this->base;

		$this->port = self::$serverDefaults['SERVER_PORT'] = $port;
		if(preg_match("|^\d+(\.\d+){3}$|",$addr,$match)){
			$this->addr = $addr;
			$this->name = gethostbyaddr($addr);
		}
		else{
			$this->name = $addr;
			$this->addr = gethostbyname($addr);
		}
		self::$serverDefaults['SERVER_NAME'] = $this->name;
		self::$serverDefaults['HTTP_HOST'] = $this->name;
	}

	public function callback(EventHttpRequest $r){

		// INCOMING REQUEST
		$this->reqHeaders = $r->getInputHeaders();
		$in = $r->getInputBuffer();

		$contentType = explode('/',($r->findHeader('Content-Type',EventHttpRequest::INPUT_HEADER)));

		$uri = $r->getUri();
		$method = self::$methods[$r->getCommand()];
		$body = $in->read($in->length);

		$parameters = $this->getParameterInfo($method, $uri, $body, $contentType);
		$cookies = $this->getCookieInfo($r);
		$server = $this->getServerInfo();

		//TODO: How do multipart file POSTs come through?
		$files = array();

		// PROCESSING
		$request = Request::create($uri, $method, $parameters, $cookies, $files, $server, $body);
		$response = $this->app->run($request);

		// OUTBOUND RESPONSE
		$headers = $response->headers->all();
		$cookies = $response->headers->getCookies();

		foreach($headers as $k=>$v){
			$r->addHeader($k, $v[0], EventHttpRequest::OUTPUT_HEADER);
		}
		$code = $response->getStatusCode();

		// Which methods should allow body in response?
		//TODO:  Confirm Symfony HTTP Foundation classes are compliant
		$out = new EventBuffer();
		$out->add($response->getContent());

		// SENDING
		$r->sendReply($code, Response::$statusTexts[$code], $out);
		$bev = $r->getBufferEvent();
		$bev->free();
	}

	private function getParameterInfo($method, $uri, $body, $contentType){
		$parameters = array();
		$components = parse_url($uri);
		$query = array();
		if(!empty($components['query'])){
			parse_str($components['query'], $query);
		}

		// Which methods should allow body in request?
		//  http://stackoverflow.com/a/299696 (2008)
		//TODO: confirm same for newer HTTP specs
		//      OR confirm HttpEvent is compliant
		switch($method){
			case 'TRACE':
				return $query;
				break;
			case 'CONNECT':
			case 'DELETE':
			case 'GET':
			case 'HEAD':
			case 'OPTIONS':
			case 'PATCH':
			case 'POST':
			case 'PUT':
				if(!empty($body)){
					switch(strtolower($contentType[1])){
						case 'json':
							$json = json_decode($body, true);
							if(is_array($json)){
								$parameters = $json;
							}
							break;
						//TODO: What about multipart?
						case 'x-www-form-urlencoded':
						case 'form-data':
						default:
							parse_str($body, $parameters);
							break;
					}
				}
				break;
			default:
				// Invalid HTTP Method
				break;
		}

		return array_replace($query, $parameters);
	}

	private function getCookieInfo(EventHttpRequest $r){
		if(empty($this->reqHeaders['Cookie'])){
			return array();
		}
		$cookies = explode(';',trim($this->reqHeaders['Cookie']));
		if(empty($cookies)){
			return array();
		}
		$info = array();
		foreach($cookies as $cookie){
			list($k,$v) = explode('=',trim($cookie));
			$info[$k] = $v;
		}
		return $info;
	}

	private function getServerInfo(){
		$server = array(
			'REQUEST_TIME'         => time(),
		);
		return array_replace(self::$serverDefaults, $server);
	}

	public function run(){
		$this->http->bind($this->addr, $this->port);
		$this->loop();
	}

	private function dispatch(){
		$this->base->dispatch();
	}

	private function loop(){
		$this->base->loop();
	}

	private function E($val){
		error_log(var_export(json_encode($val,JSON_PRETTY_PRINT),true));
	}

}
