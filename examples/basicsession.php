<?php

require_once(dirname(__DIR__).'/vendor/autoload.php');

use Spas\ApplicationServer;
use Spas\SilexApp;
use Spas\FileSessionHandler;
use Spas\SpasSessionProvider;
use Symfony\Component\HttpFoundation\Request;



$app = new SilexApp();




// initialize session stuff
$app['session.options'] = array(
	'name'=>session_name(),
	'cookie_lifetime'=>0,
	'cookie_path'=>'/',
	'cookie_domain'=>'localhost',
	'cookie_secure'=>false,
	'cookie_httponly'=>false,
	'save_path'=>'/phpsessions'
);

$app['session.handler'] = new FileSessionHandler($app['session.options']);

$app->register(new SpasSessionProvider());




// kernel events - for now we're saving session here
$app->finish(function(Request $request) use($app) {
	$request->getSession()->save();
});




// routes
$app->get('/hello/{value}', function($value,Request $request) use($app){
	$sess = $request->getSession();
	$sess->set('name',$value);
	$sid = $sess->getId();
	$name = $sess->getName();
	return <<<RESPONSE

   We will call you: $value

   NOW RUN THIS FROM COMMAND LINE:
   > curl -XGET http://localhost:8888/hello --cookie "{$name}={$sid}"

RESPONSE;
});

$app->get('/hello', function(Request $request) use($app){
	$name = $request->getSession()->get('name');
	if(empty($name)){
		return "Sorry. I couldn't find your session name.".PHP_EOL;
	}
	else{
		return 'Hello '.$name.PHP_EOL;
	}
});




echo 'NOW RUN THIS FROM COMMAND LINE:'.PHP_EOL;
echo '> curl -XGET http://localhost:8888/hello/YOURNAME'.PHP_EOL;


// instantiate server
$server = new ApplicationServer('localhost',8888,$app);
$server->run();

