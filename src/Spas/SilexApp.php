<?php

namespace Spas;

use \Silex\Application;
use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Session\Session;
use \Symfony\Component\HttpKernel\HttpKernelInterface;
use \Spas\ApplicationInterface;
use \Spas\SilexApp;
use \Spas\SpasSessionStorage;
use \Spas\SpasSessionServiceProvider;

class SilexApp extends Application implements ApplicationInterface
{
	public function __construct(array $values = array()){
		parent::__construct($values);
	}

	public function run(Request $request = null){
		$response = $this->handle($request);
		$this->terminate($request, $response);
		return $response;
	}
}
