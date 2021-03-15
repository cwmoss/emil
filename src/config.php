<?php

use Psr\Container\ContainerInterface;
use function DI\factory;
use function DI\create;
use function DI\get;

function get_env() {
    return array_merge($_SERVER, getenv());
}

function get_config($env, $appbase) {
    // $conf = parse_ini_file($appbase."/emil.ini");
    $conf = [];

    $conf['base'] = $appbase . '/templates';
    $conf['etc'] = $appbase . '/etc';
    $conf['appbase'] = $appbase;

    $conf['transport'] = $env['EMIL_MAIL_TRANSPORT'];
//    $conf['jwt_secret'] = $env['EMIL_JWT_SECRET'];
    return $conf;
}

return [
    'appbase' => function () {
        return realpath(__DIR__ . '/../');
    },
    'env' => function () {
        return get_env();
    },
    'conf' => function (ContainerInterface $c) {
        dbg('+++ config load');
        return get_config($c->get('env'), $c->get('appbase'));
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
    emil\auth::class => create()
        ->constructor(get('env')),
];
