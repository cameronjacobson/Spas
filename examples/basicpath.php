<?php

require_once(dirname(__DIR__).'/vendor/autoload.php');

use Spas\ApplicationServer;
use Spas\SilexApp;

$app = new SilexApp();

$app->get('/hello/{name}', function($name) use($app) { 
    return 'Hello '.$app->escape($name); 
}); 


echo 'NOW RUN THIS FROM COMMAND LINE:'.PHP_EOL;
echo '> curl -XGET http://localhost:8888/hello/YOURNAME'.PHP_EOL;

$server = new ApplicationServer('0.0.0.0',8888,$app);
$server->run();
