<?php
require_once(__DIR__."/dispatcher.php");

error_reporting(E_ALL & ~E_NOTICE);

$router = new \Bramus\Router\Router();

if($BASE_URL){
   $router->setBasePath($BASE_URL);
}

dispatcher::$app = $app;

dbg("+++ incoming +++ ", $_SERVER['REQUEST_METHOD'], $router->getCurrentUri());
#dbg("+++ server vars/ env", $_SERVER, getenv());
dbg("+++ cookie", $_COOKIE['emil']);
dbg("+++ conf", $app->get("conf"));

$router->get('/', function()use($req){
  dbg("index");
	resp(['ok'=>'hello my name is emil']);
});


$router->mount('/send', function() use ($router, $app) {

  $router->post('/(\w+)/(\w+)(/\w+)?', function($org, $project, $template=null) use($app) {

    if(!$template){
        $template=$project; $project = "";
    }
      
    dbg("post project email");
    
    $api = $app->make(api\email::class, ['base'=>$app->get("base")."/$org/$project"]);
        $data = get_json_req();
        dbg("send", $org, $project, $data);
        resp($api->send($template, $data));
  });

});

$router->before('POST', '/send/(\w+)/.*', function($org) use ($router, $app) {

  dbg("AUTH send", $org);

  $hdrs = $router->getRequestHeaders();
  if(!check_api($hdrs, $app->get("env"), org_options_read($app->get("etc"), $org))){
    e401();
  }
});

$router->before('POST|GET|PUT|DELETE', '/manage/(\w+)(/.*)?', function($org) use ($router, $app) {

  dbg("AUTH manage", $org);

  $hdrs = $router->getRequestHeaders();
  if(!check_api($hdrs, $app->get("env"), org_options_read($app->get("etc"), $org))){
    e401();
  }
});

$router->mount('/manage', function() use ($router) {

  $router->get('/(\w+)', "dispatcher::templates__get_projects");
  $router->put('/(\w+)/upload/([\w.]+)', "dispatcher::templates__upload_stream");
  $router->post('/(\w+)/upload', "dispatcher::templates__upload");
  $router->delete('/(\w+)/([\w.]+)', "dispatcher::templates__delete");
  
});

$router->mount('/admin', function() use ($router) {
 
  $router->get('/orgs', "dispatcher::orgs__get_orgs");
  $router->get('/org/(\w+)', "dispatcher::orgs__get_org");
  $router->post('/org/(\w+)', "dispatcher::orgs__post_create");
  $router->delete('/org/(\w+)', "dispatcher::orgs__delete");
 
});

$router->before('GET|POST|DELETE', '/admin/.*', function() use ($router, $app, $data) {

  dbg("AUTH admin");

#e401();

  $hdrs = $router->getRequestHeaders();
  if(!check_admin($hdrs, $app->get("env"))){
    $token = check_jwt($app->get("conf")['jwt_secret'], $_COOKIE['emil']);
    if(!$token || $token['org']!=='admin'){
      e401();
    }
  }
});

$router->get("/ui", function() use($app){
    $secure = false;
    $domain = "";
    $path = "/";
    $cookieopts = ['expires'=>0, 'path'=>$path, 'domain'=>$domain, 'secure'=>$secure, 'httponly'=>true, 'samesite'=>'Strict'];
    dbg("== conf++", $app->get("conf"));
    $jwt_in = $_COOKIE['emil'];
    $jwt = check_jwt($app->get("conf")['jwt_secret'], $jwt_in);
    dbg("== jwt", $jwt);
    setcookie('emil', gen_jwt($app->get("conf")['jwt_secret'], 'admin'), $cookieopts);
    $uibase = $app->get("appbase").'/ui';
    readfile($uibase.'/index.html');
});
$router->get("/ui/([-\w.]+)", function($file) use($app){
    dbg("++ jwt cookie", $_COOKIE['emil']);
    if(preg_match('/css$/', $file)){
      header("Content-Type: text/css");
    }elseif(preg_match('/js$/', $file)){
      header("Content-Type: text/javascript");
    }
    $uibase = $app->get("appbase").'/ui';
    readfile($uibase.'/'.$file);
});
$router->set404(function() {
    dbg("-- 404");
  e404();
});

$router->run();