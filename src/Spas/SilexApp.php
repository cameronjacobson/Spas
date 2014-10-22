<?php

namespace Spas;

use \Silex\Application;
use \Symfony\Component\HttpFoundation\Request;

use \Spas\ApplicationInterface;

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
