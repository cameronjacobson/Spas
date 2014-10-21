<?php

require_once(dirname(__DIR__).'/vendor/autoload.php');

use Spaz\ApplicationServer;
use Spaz\SilexApp;

$app = new SilexApp();

$app->get('/hello', function() use($app) { 
    return 'Hello '.$app['request']->cookies->get('name');
}); 


echo 'NOW RUN THIS FROM COMMAND LINE:'.PHP_EOL;
echo '> curl -XGET http://localhost:8888/hello --cookie name=YOURNAME'.PHP_EOL;

$server = new ApplicationServer('0.0.0.0',8888,$app);
$server->run();
