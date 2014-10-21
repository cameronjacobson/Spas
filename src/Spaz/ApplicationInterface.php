<?php

namespace Spaz;

use \Symfony\Component\HttpFoundation\Request;

interface ApplicationInterface
{
	public function run(Request $request);
}
