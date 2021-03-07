<?php
namespace emil;

class org{
    
    public $name;
    public $base;
    
    function __construct($name, $base){
        $this->name = $name;
        $this->base = $base;
    }
    
    function info(){
        $files = glob($this->base."/{$this->name}/*.{txt,html,png}", GLOB_BRACE);
        $tpls = array_map(function($p){
   		    return \basename($p);
   	    },	$files);
   	    return [
   	        'name' => $this->name,
   	        'templates' => $tpls,
   	        'preferences' => []
   	    ];
    }
    
    static function index($base){
        $files = glob($base."/*", GLOB_ONLYDIR);
        $orgs = array_map(function($p){
   		    return \basename($p);
   	    },	$files);
   	    return ['orgs' => $orgs];
    }
}
