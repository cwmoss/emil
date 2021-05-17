<?php

namespace api;

class templates {
    public $org;

    public function __construct($org) {
        $this->org = $org;
    }

    public function get_templates() {
        return $this->org->info();
    }

    // {"u":{"name":["__twenty.html"],"type":["text\/html"],"tmp_name":["\/private\/var\/tmp\/phpLgnLTl"],"error":[0],"size":[8900]}}

    public function upload($org) {
        //dbg($_FILES);
        $files = normalize_files_array($_FILES);
        dbg($files);
        if ($files['u']) {
            return $this->_checkin_files($files['u'], $this->org->orgbase);
        }
        return [];
    }

    public function upload_stream($org, $name) {
        return $this->_checkin_files([stream_to_file($name)], $this->org->orgbase);
    }

    public function delete($org, $name) {
        $file = $this->org->orgbase . "/$name";
        unlink($file);
        return ['ok' => $name . ' deleted'];
    }

    public function _checkin_files($files, $base) {
        list($ok, $files) = $this->_validate_files($files, $base);
        $res = [];
        foreach ($files as $file) {
            if (file_exists($base . '/' . $file['name'])) {
                $file['op'] = 'updated';
            } else {
                $file['op'] = 'created';
            }
            rename($file['tmp_name'], $base . '/' . $file['name']);
            // unlink($file['tmp_name']);
            $res[] = ['name' => $file['name'], 'size' => $file['size'], 'op' => $file['op']];
        }
        return $res;
    }

    public function _validate_files($files, $base) {
        return [true, $files];
    }
}
