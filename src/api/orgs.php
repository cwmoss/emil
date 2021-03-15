<?php

namespace api;

use emil\org;

class orgs {
    public $conf;

    public function __construct($conf) {
        $this->conf = $conf;
    }

    public function get_orgs($org = '') {
        $base = $this->conf['base'];
        return org::index($base);
    }

    public function get_org($org = '') {
        $base = $this->conf['base'];
        return (new org($org, $base, $this->conf['etc']))->info();
    }

    public function post_update($org, $data) {
        return (new org($org, $this->conf['base'], $this->conf['etc']))->update($data);
    }

    public function post_create($name, $data = null) {
        // $p = gen_password();
        // dbg("pass $p");
        if (!$data) {
            $data = [];
        }

        $org = [
            'name' => $name,
            //	'password' => password_hash($p, PASSWORD_DEFAULT),
            'api_key' => gen_secret()
        ];
        $data_ok = array_blocklist($data, 'api_key password name');
        $org = array_merge($org, $data_ok);

        $orgdir = $this->conf['base'] . '/' . $name;

        mkdir($orgdir);
        \org_options_save($this->conf['etc'], $name, $org);

        $this->_install_starter($orgdir);

        return $org;
    }

    public function post_update_api_key($name, $data = []) {
        \org_options_update($this->conf['etc'], $name, ['api_key' => gen_secret()]);
    }

    public function delete($name) {
        $base = $this->conf['base'];
        $org = $base . "/$name";
        if (trim($base, '/') != trim($org, '/')) {
            array_map('unlink', glob("$org/*.*"));
            rmdir($org);
        }
        $etc = $this->conf['etc'] . '/' . $name . '.json';
        unlink($etc);
        return ['ok' => $name . ' deleted'];
    }

    public function _install_starter($dest) {
        $starterbase = $this->conf['base'] . '/../starter';
        foreach (explode(' ', '__basic.html welcome.html welcome.txt acmelogo-200x67.png') as $tpl) {
            copy($starterbase . '/' . $tpl, $dest . '/' . $tpl);
        }
    }
}
