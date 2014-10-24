<?php

require_once(dirname(__DIR__).'/vendor/autoload.php');

use Spas\ApplicationServer;
use Spas\SilexApp;
use Symfony\Component\HttpFoundation\Request;

$app = new SilexApp();

$app->get('/hello', function(Request $request) use($app) { 
    return 'Hello '.$request->cookies->get('name');
}); 


echo 'NOW RUN THIS FROM COMMAND LINE:'.PHP_EOL;
echo '> curl -XGET http://localhost:8888/hello --cookie name=YOURNAME'.PHP_EOL;

$server = new ApplicationServer('0.0.0.0',8888,$app);
$server->run();
