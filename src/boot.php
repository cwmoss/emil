<?php
$loader = require __DIR__.'/../vendor/autoload.php';

//$loader->addPsr4('twentyseconds\\', __DIR__."/twentyseconds");
//$loader->addPsr4('api\\', __DIR__."/api");

require_once(__DIR__."/helper.php");

ini_set('error_log', join('/', [__DIR__, '..', 'logs', 'app.log']));

dbg("app started");

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__."/../");
$dotenv->load();

$conf = parse_ini_file(__DIR__."/../emil.ini", true);
$appbase = realpath(__DIR__."/../");
$conf['basedir'] = $appbase."/templates";
$conf['etc'] = $appbase."/etc";

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


return ['conf'=>$conf];