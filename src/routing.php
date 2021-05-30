<?php
require_once __DIR__ . '/dispatcher.php';
use Siler\GraphQL;

error_reporting(E_ALL & ~E_NOTICE);

$router = new \Bramus\Router\Router();

if ($BASE_URL) {
    $router->setBasePath($BASE_URL);
}

dispatcher::$app = $app;

dbg('+++ incoming +++ ', $_SERVER['REQUEST_METHOD'], $router->getCurrentUri());
//dbg("+++ server vars/ env", $_SERVER, getenv());
dbg('+++ cookie', $_COOKIE['emil']);
dbg('+++ conf', $app->get('conf'));

$router->get('/', function () use ($req) {
    dbg('index');
    resp(['ok' => 'hello my name is emil']);
});

$router->mount('/send', function () use ($router, $app) {
    $router->post('/(\w+)/(\w+)', function ($orgname, $template) use ($app) {
        dbg('post project email');

        // $org = $app->make(emil\org::class, ['name' => $orgname]);

        $api = $app->make(api\email::class);
        $data = get_json_req();
        dbg('send', $orgname, $data, $org);
        resp($api->send($template, $data));
    });
});

$router->before('POST', '/send/(\w+)/.*', function ($orgname) use ($router, $app) {
    dbg('AUTH send', $orgname);

    $app->set('orgname', $orgname);

    $hdrs = get_req_headers($router);
    $app->make('emil\\auth')->is_authorized($hdrs, $orgname, org_options_read($app->get('etc'), $orgname));
});

$router->before('POST|GET|PUT|DELETE', '/manage/(\w+)(/.*)?', function ($orgname) use ($router, $app) {
    dbg('AUTH manage', $orgname);

    $app->set('orgname', $orgname);

    $hdrs = get_req_headers($router);
    $app->make('emil\\auth')->is_authorized($hdrs, $orgname, org_options_read($app->get('etc'), $orgname));
});

$router->mount('/manage', function () use ($router) {
    $router->get('/(\w+)', 'dispatcher::templates__get_templates');
    $router->post('/(\w+)', 'dispatcher::orgs__post_update');

    $router->post('/(\w+)/apikey/([-\w]+)', 'dispatcher::orgs__post_add_api_key');
    $router->delete('/(\w+)/apikey/([-\w]+)', 'dispatcher::orgs__delete_api_key');

    $router->put('/(\w+)/upload/([-\w.]+)', 'dispatcher::templates__upload_stream');
    $router->post('/(\w+)/upload', 'dispatcher::templates__upload');
    $router->delete('/(\w+)/([-\w.]+)', 'dispatcher::templates__delete');
});

$router->mount('/admin', function () use ($router) {
    $router->get('/orgs', 'dispatcher::orgs__get_orgs');
    $router->get('/org/(\w+)', 'dispatcher::orgs__get_org');
    $router->post('/orgs/(\w+)', 'dispatcher::orgs__post_create');
    $router->post('/org/(\w+)', 'dispatcher::orgs__post_update');
    $router->delete('/org/(\w+)', 'dispatcher::orgs__delete');
});

$router->before('GET|POST|DELETE', '/admin/.*', function () use ($router, $app, $data) {
    dbg('AUTH admin');

    $hdrs = get_req_headers($router);
    $app->make('emil\\auth')->is_authorized_admin($hdrs);
});

$router->post('/login', function () use ($app) {
    resp(
        $app->make('emil\\auth')
            ->login(get_json_req())
    );
});
$router->post('/logout', function () use ($router, $app) {
    $auth = $app->make('emil\\auth');
    $hdrs = get_req_headers($router);
    $auth->is_authorized_admin($hdrs);

    resp(
        $auth->logout()
    );
});

$router->get('/ui', function () use ($app) {
    dbg('++ send index file');
    send_file($app->get('appbase'), 'index.html');
});

$router->get("/ui/([-\w.]+)", function ($file) use ($app) {
    dbg('++ send file', $file);
    send_file($app->get('appbase'), $file);
});

$router->post("/graphql/(\w+)", function ($orgname) use ($router, $app) {
    dbg('++ graphql org', $orgname);
    $app->set('orgname', $orgname);

    $schema = include __DIR__ . '/schema.php';
    $hdrs = get_req_headers($router);
    $auth = new emil\auth(get_env(), true);
    $is_authorized = $auth->is_authorized($hdrs, $orgname, org_options_read($app->get('etc'), $orgname));
    // Give it to siler
    GraphQL\init($schema, ['app' => $app, 'is_authorized' => $is_authorized]);
});

$router->set404(function () {
    dbg('-- 404');
    e404();
});

$router->run();
