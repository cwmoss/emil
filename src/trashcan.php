<?php

$router->get('/(\w+)/(\w+)', function($org, $project) use($app) {
    $api = $app->get(templates::class);
    resp($api->get_templates($org, $project));
});

public function get_projects($org){
    	
    	$projects = array_map(function($p){
    		return \basename($p);
    	},	glob($this->conf['base']."/{$org}/*", GLOB_ONLYDIR));
    	
    	return array_merge(['projects'=>$projects],
    	    $this->get_templates($org, ""));
    	;
}