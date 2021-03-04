<?php
use api\email;
use api\templates;
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



$router->get('/', function()use($req){
    dbg("index");
	resp(['res'=>'ok']);
});


$router->mount('/email', function() use ($router, $conf, $data) {
  
  $hdrs = $router->getRequestHeaders();
  
  $router->post('/(\w+)/(\w+)/send/(\w+)', function($org, $project, $template) use($conf, $hdrs, $data) {

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

});


$router->before('GET|POST', '/manage/.*', function() use ($router, $conf, $data) {
  $hdrs = $router->getRequestHeaders();

  if(!$hdrs['x-any-admin'] || ($hdrs['x-any-admin']!=$_SERVER["XSTORE_ADMIN"])){
    dbg("+++ 401 +++ ");

    header("HTTP/1.1 401 Unauthorized");
    resp(['res'=>'fail', 'msg'=>'Unauthorized']);

    exit;
  }
});

$router->mount('/manage', function() use ($router, $conf, $data) {
  
  dbg("+++ manage +++ ");
  

  $api = new templates;
  
  $router->post('/org/(\w+)/([-\w\d]+)', function($meth, $name) use($api, $hdrs, $data) {

      $data = get_json_req();
      
      dbg("method: $meth", $name, $data);
      resp($api->$meth($name, $data, $hdrs));
  });

});

dbg("+++ setup  +++ ");

$router->set404(function() {
    header('HTTP/1.1 404 Not Found');
    resp(['res'=>'fail', 'msg'=>'not found']);
});

$router->run();