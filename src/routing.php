<?php
use api\email;
use api\templates;
use api\orgs;

error_reporting(E_ALL & ~E_NOTICE);

$router = new \Bramus\Router\Router();

if($BASE_URL){
   $router->setBasePath($BASE_URL);
}

dbg("+++ incoming +++ ", $_SERVER['REQUEST_METHOD'], $router->getCurrentUri());
dbg("+++ server vars", $_SERVER);

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

  dbg("before send", $org);

  $hdrs = $router->getRequestHeaders();

  if(!check_api($hdrs, $_SERVER, org_options_read($app->get("etc"), $org))){

    dbg("+++ 401 +++ ");

    header("HTTP/1.1 401 Unauthorized");
    resp(['res'=>'fail', 'msg'=>'Unauthorized Api Request']);

    exit;
  }
});

$router->before('POST|GET|PUT|DELETE', '/manage/(\w+)(/.*)?', function($org) use ($router, $app) {

  dbg("before manage", $org);

  $hdrs = $router->getRequestHeaders();

  dbg("+++ hdrs", $hdrs);
  
  if(!check_api($hdrs, $_SERVER, org_options_read($app->get("etc"), $org))){

    dbg("+++ 401 +++ ");

    header("HTTP/1.1 401 Unauthorized");
    resp(['res'=>'fail', 'msg'=>'Unauthorized Api Request']);

    exit;
  }
});

$router->mount('/manage', function() use ($router, $app) {

  $router->get('/(\w+)', function($org) use($app) {
     // $api = new templates($conf['conf']);
      $api = $app->get(api\templates::class);
      resp($api->get_projects($org));
  });

  $router->put('/(\w+)/upload/([\w.]+)', function($org, $name) use($app) {
      $api = $app->get(templates::class);
    dbg("PUT", $name, 'xxx', $_FILES);
    resp($api->upload_stream($org, $name));

    #  resp($api->upload_stream($org, $project, $name));
  });

  $router->post('/(\w+)/upload', function($org) use($app) {
      $api = $app->get(templates::class);
      resp($api->upload($org));
  });
  
  $router->delete('/(\w+)/([\w.]+)', function($org, $name) use($app) {
      $api = $app->get(templates::class);
      resp($api->delete($org, $name));
  });
  
});


$router->mount('/admin', function() use ($router, $app) {
 
  $router->get('/orgs', function() use ($app){
    $api = $app->get(orgs::class);
    $meth = 'get_'.$meth;
    resp($api->get_orgs());
  });

  $router->get('/org/(\w+)', function($name) use ($app){
      $api = $app->get(orgs::class);
      $meth = 'get_'.$meth;
      resp($api->get_org($name));
    });
    
  $router->post('/org/(\w+)', function($name) use ($app){
    $api = $app->get(orgs::class);
    $meth = 'post_'.$meth;
    resp($api->post_create($name, get_json_req()));
  });
  
  $router->delete('/org/(\w+)', function($name) use ($app){
      $api = $app->get(orgs::class);
      resp($api->delete($name));
    });
  
});

$router->before('GET|POST|DELETE', '/admin/.*', function() use ($router, $conf, $data) {

  dbg("before admin");

  $hdrs = $router->getRequestHeaders();
  dbg("headers:", $hdrs, $_SERVER);

  if(!check_admin($hdrs, $_SERVER)){

    dbg("+++ 401 +++ ");

    header("HTTP/1.1 401 Unauthorized");
    resp(['res'=>'fail', 'msg'=>'Unauthorized']);

    exit;
  }
});

$router->set404(function() {
    header('HTTP/1.1 404 Not Found');
    resp(['res'=>'fail', 'msg'=>'not found']);
});

$router->run();