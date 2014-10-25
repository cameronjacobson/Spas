<?php

require_once(dirname(__DIR__).'/vendor/autoload.php');

use Spas\ApplicationServer;
use Spas\SilexApp;

$app = new SilexApp();

$app->get('/asynchello', function() use($app){
	async($app['base'], function(){
		error_log('hello world');
	}, 5000);
	return '';
});

echo 'NOW RUN THIS FROM COMMAND LINE:'.PHP_EOL;
echo '> curl -XGET http://localhost:8888/asynchello'.PHP_EOL;

$server = new ApplicationServer('0.0.0.0',8888,$app);
$server->run();



function async($base, $fn, $ms){
	$e = Event::timer($base, function($args) use(&$e, $fn, $ms) {
		$fn($args);
	});
	$e->addTimer($ms/1000);
}

