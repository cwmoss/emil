<?php
$loader = require __DIR__.'/../vendor/autoload.php';
$loader->addPsr4('twentyseconds\\', __DIR__."/twentyseconds");
$loader->addPsr4('api\\', __DIR__."/api");

require_once(__DIR__."/helper.php");

ini_set('error_log', join('/', [__DIR__, '..', 'logs', 'app.log']));

dbg("app started");

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__."/../");
$dotenv->load();

$conf = parse_ini_file(__DIR__."/../emil.ini", true);
$conf['basedir'] = realpath(__DIR__."/../templates");


function api_exception_handler($e){

   $class = get_class($e);
   $pclass = get_parent_class($e);
   $m=$e->getMessage();

   $fm = sprintf("%s:\n   %s line: %s code: %s\n   via %s%s\n", $m, $e->getFile(), $e->getLine(),
        $e->getCode(), $class, $pclass?', '.$pclass:''
    );
   $trace .= $fm.$e->getTraceAsString();

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