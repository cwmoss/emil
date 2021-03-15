<?php

namespace emil;

class org {
    public $name;
    public $base;
    public $etc;

    public function __construct($name, $base, $etc) {
        $this->name = $name;
        $this->base = $base;
        $this->etc = $etc;
    }

    public function update($data) {
        dbg('+++ update etc for ', $this->name, $data);
        org_options_update($this->etc, $this->name, $data);
        return $this->info();
    }

    public function info() {
        $files = glob($this->base . "/{$this->name}/*.{txt,html,png}", GLOB_BRACE);
        $tpls = array_map(function ($p) {
            return $this->template_info($p);
        }, $files);
        return [
            'name' => $this->name,
            'templates' => $tpls,
            'preferences' => $this->preferences()
        ];
    }

    public static function index($base) {
        $files = glob($base . '/*', GLOB_ONLYDIR);
        $orgs = array_map(function ($p) {
            return \basename($p);
        }, $files);
        return ['orgs' => $orgs];
    }

    public function preferences() {
        dbg('+++ prefs for ', $this->name);
        $p = org_options_read($this->etc, $this->name);
        return $p;
    }

    public function template_info($file) {
        return [
            'name' => \basename($file),
            'size' => \filesize($file),
            'modified_at' => date('Y-m-d H:i:s', filemtime($file))
        ];
    }
}
