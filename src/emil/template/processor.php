<?php

namespace emil\template;

/*
    $processor = new twentyseconds\template\processor(__DIR__."/templates/$org/$project",
      [
        'frontparser'=>$parser,
        'layout' => 'layout'
    ]);
*/

use LightnCandy\LightnCandy;

class processor {
    public $basedir;
    public $opts;

    public $name;
    public $stage = [];

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
    public $embeds = [];

    public $types = ['txt', 'html'];

    public function __construct($basedir, $opts = ['']) {
        $this->basedir = $basedir;
        $this->opts = $opts;
    }

    public function process($name, $data) {
        $this->tfiles = [];

        return $this
            ->load_template($name)
            ->parse_data()
            ->compile_template($data)
            ->run($data);

        // remove
        return array_map(function ($el) {
            return $el['x'];
        }, $this->tfiles);
    }

    public function load_template($name) {
        $this->stage[] = 'load';

        foreach ($this->types as $type) {
            $fname = join('/', [$this->basedir, $name . '.' . $type]);
            if (file_exists($fname)) {
                $this->tfiles[$type] = [
                    'n' => $fname,
                    'c' => file_get_contents($fname)
                ];
            }
        }
        return $this;
    }

    public function parse_data() {
        $this->stage[] = 'parse';

        foreach ($this->tfiles as $type => $tpl) {
            $document = $this->opts['frontparser']->parse($tpl['c'], false);
            $this->tfiles[$type]['d'] = $document->getYAML() ?? [];
            $this->tfiles[$type]['t'] = $document->getContent() ?? '';
        }
        return $this;
    }

    public function compile_template($data) {
        $this->stage[] = 'compile';
        $base = $this->basedir;

        $layoutname = $data['layout'] ?: $this->tfiles['txt']['d']['layout'] ?: $this->opts['layout'];
        if ($layoutname) {
            $layoutname = '__' . $layoutname;
        }

        foreach ($this->tfiles as $type => $tpl) {
            if ($layoutname && file_exists($base . '/' . $layoutname . '.' . $type)) {
                $src = sprintf("{{#> %s }}\n%s\n{{/ %s }}", $layoutname, $tpl['t'], $layoutname);
            } else {
                $src = $tpl['t'];
            }

            print "source: $src\n";

            $processor = $this;

            $this->tfiles[$type]['r'] = LightnCandy::compile(
                $src,
                [
                    'partialresolver' => function ($cx, $name) use ($base, $type) {
                        $fname = "$base/{$name}.{$type}";
                        print "... resolving file $name $tf => $fname";
                        // print_r($cx);
                        if (file_exists($fname)) {
                            print 'ok';
                            return file_get_contents($fname);
                        }
                        return "[partial (file:$fname) not found]";
                    },
                    'helpers' => [
                        'embed' => function ($context, $options) use ($processor) {
                            // im compile step nix tun,
                            // erst im runstep wird die embedliste produziert
                            return $context;
                        }
                    ],
                    'flags' => LightnCandy::FLAG_ERROR_LOG | LightnCandy::FLAG_RUNTIMEPARTIAL
                ]
            );
        }

        return $this;
    }

    public function run($data) {
        $this->stage[] = 'run';
        $base = $this->basedir;
        $processor = $this;

        print_r($data);
        $res = [];
        foreach ($this->tfiles as $type => $tpl) {
            $renderer = eval($tpl['r']);
            $res[$type] = $renderer($data, [
                'helpers' => [
                    'embed' => function ($context, $options) use ($processor) {
                        $out = '';
                        $data = $options['data'];

                        $file = $processor->basedir . '/' . $context;
                        $hash = 'embed-' . md5($file) . '-embed';
                        $processor->embeds[$hash] = $file;
                        dbg('++ embed runtime', $context, $hash, $file);
                        //foreach ($context as $idx => $cx) {
                        //    $data['index'] = $idx;
                        //    $out .= $options['fn']($cx, array('data' => $data));
                        //}

                        return $hash;
                    }
                ],
            ]);
        }
        dbg('++ embedliste', $this->embeds);

        $res['embeds'] = $this->embeds;
        return $res;
    }

    public function get_data($key) {
        return $this->tfiles['txt']['d'][$key];
    }

    public function process_string($str, $data) {
        $r = eval(LightnCandy::compile($str));
        return $r($data);
    }
}
