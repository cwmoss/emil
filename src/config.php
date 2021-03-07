<?php

use Psr\Container\ContainerInterface;
use function DI\factory;
use function DI\create;
use function DI\get;

function get_config(){
    $appbase = realpath(__DIR__."/../");
    $conf = parse_ini_file($appbase."/emil.ini");
    
    $conf['base'] = $appbase."/templates";
    $conf['etc'] = $appbase."/etc";
    $conf['appbase'] = $appbase;
    return $conf;
}
return [
    'conf' => function(){
        dbg("+++ config load");
        return get_config();
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
            get(twentyseconds\mailer::class),
            get('frontparser')
        ),
    api\templates::class => create()
        ->constructor(get('conf')),
    api\orgs::class => create()
        ->constructor(get('conf')),
    emil\mailer::class => create()
        ->constructor(get('conf')),

];