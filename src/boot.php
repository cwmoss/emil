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

return ['conf'=>$conf];