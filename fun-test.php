<?php
$loader = require __DIR__.'/vendor/autoload.php';
$app = require("src/boot.php");

use function twentyseconds\template\{process, process_string, get_data};


$frontparser = $app->get("frontparser");
$base = $app->get("base");

$data = ['name'=>'robert'];
$template = "welcome";

$views = process($template, $data, [
    'base'=> $base."/acme",
    'frontparser' => $frontparser,
    'types' => ['txt', 'html']
    ]);
    
#print_r($views);

$api = $app->make(api\email::class, ['base'=>'/bumm']);
print_r($api);
dbg("heho");