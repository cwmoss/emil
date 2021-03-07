<?php
$loader = require __DIR__.'/../vendor/autoload.php';

if(PHP_SAPI=='cli'){
    $log = '/dev/stdout';
}else{
    $log = join('/', [__DIR__, '..', 'logs', 'app.log']);
}
/*
embeded php webserver (with router script) handles dots in URIs different
php -S localhost:1199 -t public/ public/index.php

without dots
    /manage/acme/upload/test =>
        "REQUEST_URI": "\/manage\/acme\/upload\/test",
    	"REQUEST_METHOD": "PUT",
    	"SCRIPT_NAME": "\/index.php",
    	"SCRIPT_FILENAME": "\/Users\/rw\/dev\/emil\/public\/index.php",
with dots
    /manage/acme/upload/test.jpg =>
        "REQUEST_URI": "\/manage\/acme\/upload\/test.jpg",
    	"REQUEST_METHOD": "PUT",
    	"SCRIPT_NAME": "\/manage\/acme\/upload\/test.jpg",
    	"SCRIPT_FILENAME": "public\/index.php",
*/
if(PHP_SAPI=='cli-server'){
    if(strpos($_SERVER['REQUEST_URI'], '.')!==false){
        dbg("+++ env hack!");
        $_SERVER['SCRIPT_NAME'] = '/'.basename($_SERVER['SCRIPT_FILENAME']);
    }
}

ini_set('error_log', $log);

dbg("app started", PHP_SAPI);

$builder = new DI\ContainerBuilder();
$builder->useAutowiring(false);
$builder->useAnnotations(false);
$builder->addDefinitions(__DIR__.'/config.php');
$app = $builder->build();

Dotenv\Dotenv::createImmutable($app->get("appbase"))->load();


function api_exception_handler($e){

	$trace = get_trace_from_exception($e);

   print json_encode(['exception'=>$trace]);
}

function api_error_handler($fehlercode, $fehlertext, $fehlerdatei, $fehlerzeile){
    if (!(error_reporting() & $fehlercode)) {
        // Dieser Fehlercode ist nicht in error_reporting enthalten
        return false;
    }
    throw new ErrorException($fehlertext, 0, $fehlercode, $fehlerdatei, $fehlerzeile);
}

set_exception_handler('api_exception_handler');
set_error_handler('api_error_handler');


return $app;