<?php

namespace Spas;

use \Symfony\Component\HttpFoundation\Request;
use \Spas\ApplicationInterface;

interface ApplicationInterface
{
	public function run(Request $request);
}
