<?php

use Psr\Container\ContainerInterface;
use function DI\factory;
use function DI\create;
use function DI\get;

function get_env(){
    return array_merge($_SERVER, getenv());
}

function get_config($env){
    $appbase = realpath(__DIR__."/../");
    // $conf = parse_ini_file($appbase."/emil.ini");
    $conf = [];

    $conf['base'] = $appbase."/templates";
    $conf['etc'] = $appbase."/etc";
    $conf['appbase'] = $appbase;

    $conf['transport'] = $env['EMIL_MAIL_TRANSPORT'];

    return $conf;
}

return [
    'env' =>function(){
        return get_env();
    },
    'conf' => function(ContainerInterface $c){
        dbg("+++ config load");
        return get_config($c->get('env'));
    },
    'appbase' => function (ContainerInterface $c) {
        return $c->get('conf')['appbase'];
    },
    'base' => function (ContainerInterface $c) {
        return $c->get('conf')['base'];
    },
    'etc' => function (ContainerInterface $c) {
        return $c->get('conf')['etc'];
    },
    'frontparser' => create('Mni\FrontYAML\Parser'),
    api\email::class => create()
        ->constructor(
            get(emil\mailer::class),
            get('frontparser')
        ),
    api\templates::class => create()
        ->constructor(get('conf')),
    api\orgs::class => create()
        ->constructor(get('conf')),
    emil\mailer::class => create()
        ->constructor(get('conf')),

];