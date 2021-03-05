<?php
use api\email;
use api\templates;
use api\orgs;

use twentyseconds\mailer;

// list($post, $raw) = get_json_req();
if($_SERVER['REQUEST_METHOD']=="POST")
    $data = get_json_req();


dbg("+++ incoming +++ ");

error_reporting(E_ALL & ~E_NOTICE);

$router = new \Bramus\Router\Router();

if($BASE_URL){
   $router->setBasePath($BASE_URL);
}

dbg("+++ server vars", $_SERVER);

$router->get('/', function()use($req){
    dbg("index");
	resp(['res'=>'ok']);
});



$router->mount('/send', function() use ($router, $conf, $data) {
  
  $hdrs = $router->getRequestHeaders();
  
  dbg("mount email");

  $router->post('/(\w+)/(\w+)/(\w+)', function($org, $project, $template) use($conf, $hdrs, $data) {

    dbg("post project email");

    $mailer = new mailer($conf['conf']['_']);
    $parser = new Mni\FrontYAML\Parser();

    $processor = new twentyseconds\template\processor(__DIR__."/templates/$org/$project", 
      [
        'frontparser'=>$parser,
        'layout' => 'layout'
    ]);

      $api = new email($mailer, $processor);

      dbg("send", $org, $project, $data);
      resp($api->send($template, $data, $hdrs));
  });

  $router->post('/(\w+)/(\w+)', function($org, $template) use($conf, $hdrs, $data) {

    dbg("post email");

    $mailer = new mailer($conf['conf']['_']);
    $parser = new Mni\FrontYAML\Parser();

    $processor = new twentyseconds\template\processor(__DIR__."/templates/$org", 
      [
        'frontparser'=>$parser,
        'layout' => 'layout'
    ]);

      $api = new email($mailer, $processor);

      dbg("send", $org, $project, $data);
      resp($api->send($template, $data, $hdrs));
  });

});

$router->before('POST', '/send/(\w+)/.*', function($org) use ($router, $conf, $data) {

  dbg("before send");

  $hdrs = $router->getRequestHeaders();
  dbg("headers:", $hdrs, $_SERVER);

  if(!check_api($hdrs, $_SERVER, org_options_read($conf['conf']['etc'], $org))){

    dbg("+++ 401 +++ ");

    header("HTTP/1.1 401 Unauthorized");
    resp(['res'=>'fail', 'msg'=>'Unauthorized Api Request']);

    exit;
  }
});

$router->mount('/manage', function() use ($router, $conf, $data) {
  
  dbg("+++ manage +++ ");
  dbg($conf);

  $api = new templates($conf['conf']);
  
  $router->get('/(\w+)/', function($org) use($api, $conf, $hdrs, $data) {
      resp($api->get_projects($org));
  });

  $router->get('/(\w+)/(\w+)/', function($org, $project) use($api, $conf, $hdrs, $data) {
      resp($api->get_templates($org, $project));
  });

  $router->post('/(\w+)/(\w+)/upload', function($org, $project) use($api, $conf, $hdrs, $data) {
      resp($api->upload($org, $project));
  });

  $router->put('/(\w+)/(\w+)/upload/([\w.]+)', function($org, $project, $name) use($api, $conf, $hdrs, $data) {

    // dbg("PUT", $name, file_get_contents('php://input'), $_FILES);
    resp([]);

    #  resp($api->upload_stream($org, $project, $name));
  });

  $router->post('/org/(\w+)/([-\w\d]+)', function($meth, $name) use($api, $hdrs, $data) {

      $data = get_json_req();
      
      dbg("method: $meth", $name, $data);
      resp($api->$meth($name, $data, $hdrs));
  });

});

$router->before('GET|POST', '/admin/.*', function() use ($router, $conf, $data) {

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

$router->mount('/admin', function() use ($router, $conf, $data) {
  
  dbg("+++ admin +++ ");
  dbg($conf);

  $api = new orgs($conf['conf']);
  
  $router->get('/(\w+)', function($meth) use ($api){
    $meth = 'get_'.$meth;
    resp($api->$meth());
  });

  $router->post('/(\w+)/(\w+)', function($meth, $name) use ($api, $data){
    $meth = 'post_'.$meth;
    resp($api->$meth($name, $data));
  });
});

$router->set404(function() {
    header('HTTP/1.1 404 Not Found');
    resp(['res'=>'fail', 'msg'=>'not found']);
});

$router->run();