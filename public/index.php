<?php
# phpinfo();
$base = __DIR__."/../";
$app = require_once($base."src/boot.php");

#var_dump($conf);

dbg("+++ start +++ ");

// if there is no url rewriting active or
// the server is not the php server mode
// than we need to give the router some hints
// on what is the baseurl
if(preg_match("/index\.php/", $_SERVER['REQUEST_URI'])){
	$BASE_URL=$_SERVER['SCRIPT_NAME'].'/';
}

require_once($base."src/routing.php");


