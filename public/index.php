<?php
# phpinfo();
$conf = require_once(__DIR__."/../src/boot.php");

var_dump($conf);

dbg("+++ start +++ ");

$BASE_URL=$_SERVER['SCRIPT_NAME'].'/';

require_once(__DIR__."/../http.php");


