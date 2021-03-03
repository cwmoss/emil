<?php
namespace twentyseconds\template;


use LightnCandy\LightnCandy;

class processor{
	
	public $basedir;
	public $opts;

	public $name;
	public $stage=[];

	/*
	 n filename
	 c filecontent
	 d filedata
	 t templatecontent
	 r renderable template
	 x result
	*/
	public $tfiles = [];
	public $layouts = [];

	public $types = ['txt', 'html'];

	function __construct($basedir, $opts=['']){
		$this->basedir = $basedir;
		$this->opts = $opts;
	}

	function process($name, $data){
		$this->tfiles = [];
		
		return $this
			->load_template($name)
			->parse_data()
			->compile_template($data)
			->run($data);

// remove
		return array_map(function($el){
			return $el['x'];
		},	$this->tfiles);
	}

	function load_template($name){
		$this->stage[] = "load";
		
		foreach($this->types as $type){

			$fname = join("/", [$this->basedir, $name.'.'.$type]);
			if(file_exists($fname)){
				$this->tfiles[$type] = [
					'n' => $fname,
					'c' => file_get_contents($fname)
				];
			}
		}
		return $this;	
	}

	function parse_data(){
		$this->stage[] = "parse";

		foreach($this->tfiles as $type=>$tpl){
			$document = $this->opts['frontparser']->parse($tpl['c'], false);
			$this->tfiles[$type]['d'] = $document->getYAML()??[];
			$this->tfiles[$type]['t'] = $document->getContent()??"";
		}
		return $this;
	}

	function compile_template($data){
		$this->stage[] = "compile";
		$base = $this->basedir;

		$layoutname = $data['layout']?:$this->tfiles['txt']['d']['layout']?:$this->opts['layout'];
		if($layoutname) $layoutname = '_'.$layoutname;

		foreach($this->tfiles as $type=>$tpl){

			if($layoutname){
				$src = sprintf("{{#> %s }}\n%s\n{{/ %s }}", $layoutname, $tpl['t'], $layoutname);
			}else{
				$src = $tpl['t'];
			}
			
			print "source: $src\n";

			$this->tfiles[$type]['r'] = LightnCandy::compile($src, array(
			    'partialresolver' => function ($cx, $name) use($base, $type){
			    	$fname = "$base/{$name}.{$type}";
			    	print "... resolving file $name $tf => $fname";
			    	// print_r($cx);
			        if (file_exists($fname)) {
			        		print "ok";
			            return file_get_contents($fname);
			        }
			        return "[partial (file:$fname) not found]";
			    },
			     'flags' => LightnCandy::FLAG_ERROR_LOG | LightnCandy::FLAG_RUNTIMEPARTIAL
				)
			);
		}
		return $this;
	}

	function run($data){
		$this->stage[] = "run";
		$base = $this->basedir;

		$layoutname = $data['layout'];

		$res = [];
		foreach($this->tfiles as $type=>$tpl){
			$renderer = eval($tpl['r']);
			$res[$type] = $renderer($data);
		}
		return $res;
	}

	function get_data($key){
		return $this->tfiles['txt']['d'][$key];
	}
	
	function process_string($str, $data){

		$r = eval(LightnCandy::compile($str));
		return $r($data);

	}
}