<?php

namespace emil;

class org {
    public $name;
    public $base;
    public $etc;
    public $orgbase;

    public function __construct($base, $etc, $name) {
        $this->name = $name;
        $this->base = $base;
        $this->etc = $etc;
        $this->orgbase = "{$this->base}/{$this->name}/";
    }

    public function update($data) {
        dbg('+++ update etc for ', $this->name, $data);
        org_options_update($this->etc, $this->name, $data);
        return $this->info();
    }

    public function info() {
        $files = glob($this->base . "/{$this->name}/*.{md,txt,html,png,jpg}", GLOB_BRACE);
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
