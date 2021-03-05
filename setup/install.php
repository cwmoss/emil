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

	print "... ok\n";
}

$msecret = $secret?:"your-admin-secret-insert-here";

$msg = <<<EMSG
	
	/etc, /logs and /templates must be writeable by the server, please check

	next you should enter your SMTP details in .env file

	you can start your local server now
	php -S localhost:1199 -t public/

	you can start by creating an organization ("acme")
	curl -v localhost:1199/admin/create/acme -X POST -H "X-Emil-Admin: {$msecret}"

	this will create a api key for acme plus some templates to get startet
	you can now send a message ("welcome") to yourself like this:

	curl -v localhost:1199/send/acme/welcome -X POST \
		-H "X-Emil-Api: acme-api-key-insert-here" \
		-d '{"to":"youremail@yourserver.net","from":"youremail@yourserver.net",{"d":{"name":"strange guy"}}'

	have fun!  


EMSG;

print $msg;