<?php
namespace api;


class templates{
	
	public $conf;

	function __construct($conf, $processor=null){
        $this->conf = $conf;
        $this->processor = $processor;
   }

   public function get_projects($org){
   	dbg("++", $this->conf['basedir']."/{$org}/*");
   	$projects = array_map(function($p){
   		return \basename($p);
   	},	glob($this->conf['basedir']."/{$org}/*", GLOB_ONLYDIR));
   	return ['projects'=>$projects];
   }

	public function get_templates($org, $project){

		$templates = array_map(function($p){
   		return \basename($p);
   	},	glob($this->conf['basedir']."/{$org}/{$project}/*"));

   	return ['templates'=>$templates];
	}

// {"u":{"name":["__twenty.html"],"type":["text\/html"],"tmp_name":["\/private\/var\/tmp\/phpLgnLTl"],"error":[0],"size":[8900]}}

	public function upload($org, $project){
		#dbg($_FILES);
		$files = normalize_files_array($_FILES);
		dbg($files);
		if($files['u']){
			return $this->_checkin_files($files['u'], $this->conf['basedir']."/{$org}/{$project}");
		}
		return [];
	}

	public function upload_stream($org, $project, $name){
		return $this->_checkin_files([stream_to_file($name)], $this->conf['basedir']."/{$org}/{$project}");
	}

	public function _checkin_files($files, $base){
		list($ok, $files) = $this->_validate_files($files, $base);
		$res = [];
		foreach($files as $file){
			if(file_exists($base."/".$file['name'])){
				$file['op'] = 'updated';
			}else{
				$file['op'] = 'created';
			}
			rename($file['tmp_name'], $base."/".$file['name']);
			// unlink($file['tmp_name']);
			$res[] = ['name'=>$file['name'], 'size'=>$file['size'], 'op'=>$file['op']];
		}
		return $res;
	}

	public function _validate_files($files, $base){
		return [true, $files];
	}

	public function create($name, $data=[], $hdrs=[]){
		$p = gen_password();
		dbg("pass $p");
		$org = [
			'name' => $name,
			'password' => password_hash($p, PASSWORD_DEFAULT),
			'api_key' => gen_secret(),
			'types' => "{}",
		];
		$ok = $this->db->insert('orgs', $org);
		$org['password'] = $p;
		return $org;
	}
}