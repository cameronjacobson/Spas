<?php

// simple client to test for memory leaks
for($x=0;$x<10000;$x++){
	$url = 'http://localhost:8888/hello/blah'.$x;
	$ch = curl_init();
	curl_setopt($ch,CURLOPT_URL, $url);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
	$response = curl_exec($ch);
	curl_close($ch);
	var_dump($response);
}
