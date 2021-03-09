<?php

class dispatcher{
  
  public static $app;

  public static function __callStatic($m, $args){
    list($class, $meth) = explode('__', $m);
    $api = self::$app->get("api\\{$class}");
    if(preg_match("/^post_/", $meth)){
      $args[] = get_json_req();
    }
    $res = $api->$meth(...$args);
    resp($res);
  }

}