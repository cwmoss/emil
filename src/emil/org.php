<?php
namespace emil;

class org
{
    public $name;
    public $base;
    
    public function __construct($name, $base)
    {
        $this->name = $name;
        $this->base = $base;
    }
    
    public function info()
    {
        $files = glob($this->base."/{$this->name}/*.{txt,html,png}", GLOB_BRACE);
        $tpls = array_map(function ($p) {
            return \basename($p);
        }, $files);
        return [
            'name' => $this->name,
            'templates' => $tpls,
            'preferences' => []
        ];
    }
    
    public static function index($base)
    {
        $files = glob($base."/*", GLOB_ONLYDIR);
        $orgs = array_map(function ($p) {
            return \basename($p);
        }, $files);
        return ['orgs' => $orgs];
    }
}
