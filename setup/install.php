<?php
$base = __DIR__."/../";

require_once($base."src/helper.php");

$envf=$base.".env";

if(!file_exists($envf)){
	$secret = gen_secret();
	
	print "generating EMIL_ADMIN_SECRET:\n$secret\n";
	print "createing .env from dot.env.example\n";

	file_put_contents($envf, str_replace(['super-secret-admin-key'], [$secret], file_get_contents($base."dot.env.example")));
}else{
	print ".env found in root folder, skip\n";
}

$writeable = [
	'etc' => $base."etc",
	'logs' => $base."logs",
	'templates' => $base."templates",
];

foreach($writeable as $name => $dir){

	print "checking $name ($dir) ";
	if(!file_exists($dir)){
		print "... creating ";
		mkdir($dir);
	}

	if(!is_dir($dir)){
		print "... not a directory. please make it a writeable directory\n";
		continue;
	}

	print "\n";
}


