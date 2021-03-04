<?php

function resp($data){
  header("content-type: application/json");
  print json_encode($data);

  dbg("+++ finished");
}

function dbg($txt, ...$vars){
// im servermodus wird der zeitstempel automatisch gesetzt
//	$log = [date('Y-m-d H:i:s')];
	$log= [];
   if(!is_string($txt)){
   	array_unshift($vars, $txt);
   }else{
      $log[] = $txt;
   }
   $log[] = join(' ', array_map('json_encode', $vars));
	error_log(join(' ', $log));
}

function get_json_and_raw_req(){
  $raw = get_raw_req();
	$post = json_decode($raw, true);
	return [$post, $raw];
}

function get_json_req(){
  return json_decode(get_raw_req(), true);
}

function get_raw_req(){
  dbg("++++ raw input read ++++");
  return file_get_contents('php://input');
}

function url_to_pdo_dsn($url){
    $parts = parse_url($url);

    return [
        $parts['scheme'].':host='.$parts['host'].';dbname='.trim($parts['path'], '/'),
        $parts['user'],
        $parts['pass']
    ];
}

function gen_secret(){
  return bin2hex(random_bytes(32));
}

function gen_password($len=15){
  $chars =  'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'.
            '0123456789-!?@#$%#';

  $str = '';
  $max = strlen($chars) - 1;

  for ($i=0; $i < $len; $i++)
    $str .= $chars[random_int(0, $max)];

  return $str;
}
/*
password_verify ( string $password , string $hash )
*/

function normalize_files_array($files = []) {

    $normalized_array = [];

    foreach($files as $index => $file) {

        if (!is_array($file['name'])) {
            $normalized_array[$index][] = $file;
            continue;
        }

        foreach($file['name'] as $idx => $name) {
            $normalized_array[$index][$idx] = [
                'name' => $name,
                'type' => $file['type'][$idx],
                'tmp_name' => $file['tmp_name'][$idx],
                'error' => $file['error'][$idx],
                'size' => $file['size'][$idx]
            ];
        }

    }

    return $normalized_array;
}

function stream_to_file($name){
  $tmpfname = tempnam(sys_get_temp_dir(), 'emil-');
  file_put_contents($tmpfname, file_get_contents('php://input'));
  return [
    'name' => $name,
    'type' => 'stream',
    'tmp_name' => $tmpfname,
    'error' => 0,
    'size' => filesize($tmpfname)
  ];
}