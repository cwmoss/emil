<?php
namespace api;


class orgs{
	
	public $conf;

	public function __construct($conf){
		$this->conf = $conf;
	}

	public function get_orgs(){
		$orgs = array_map(function($p){
   		return \basename($p);
   	},	glob($this->conf['basedir']."/*", GLOB_ONLYDIR));
   	return ['orgs'=>$orgs]; 
	}

	public function post_create($name, $data=[]){
		// $p = gen_password();
		dbg("pass $p");
		$org = [
			'name' => $name,
			'password' => password_hash($p, PASSWORD_DEFAULT),
			'api_key' => gen_secret()
		];
		$orgdir = $this->conf['basedir'].'/'.$name;
		
		mkdir($orgdir);
		\org_options_save($this->conf['etc'], $name, $org);

		$this->_install_starter($orgdir);

		return $org;
	}

	public function post_update_api_key($name, $data=[]){
		\org_options_update($this->conf['etc'], $name, ['api_key'=>gen_secret()]);
	}

	public function _install_starter($dest){
		$starterbase = $this->conf['basedir']."/../starter";
		foreach(explode(" ", "__basic.html welcome.html welcome.txt acmelogo-200x67.png") as $tpl){
			copy($starterbase."/".$tpl, $dest.'/'.$tpl);
		}
	}

}