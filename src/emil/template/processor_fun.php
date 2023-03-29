<?php

namespace emil\template;

use LightnCandy\LightnCandy;

function process($name, $data, $opts) {
    $templates = array_map(function ($type) use ($name, $opts) {
        $t = load_template($opts['base'], $name, $type);
        $parsed = [];
        if ($t['x']) {
            $parsed = parse_yaml($t['z'], $opts['frontparser']);
        }
        return array_merge($t, $parsed);
    }, $opts['types']);

    // dd('templates', $templates);
    // only data-options
    $opts_data = array_blocklist($opts, 'api_key password transport frontparser base types');

    //$layout = pick_layout($data, $templates[0]['d'],
    //    $templates[1]['d'], $opts);
    $templates = unfold_md_template($templates, $opts);

    $data = array_merge($opts_data, $templates[1]['d'] ?? [], $templates[0]['d'] ?? [], $data);
    $layout = name_layout($data['layout']);

    $templates = array_map(function ($t) use ($layout, $opts) {
        $src = add_layout_tag($t['b'], $t['c'], $layout, $t['t']);
        $t['c2'] = $src;
        $runner = compile($src, $t, $opts['helper']);
        $t['run'] = $runner;
        return $t;
    }, $templates);

    $templates = array_map(function ($t) use ($data) {
        $res = run($t['run'], $data, $t);
        return array_merge($t, $res);
    }, $templates);

    $templates = array_map(fn ($t) => post_process($t, $opts), $templates);

    return [$templates, $data];
}

function unfold_md_template($templates) {
    // kein md template?
    if (!$templates[0]['x']) {
        \array_shift($templates);
        return $templates;
    }
    $unfold = [$templates[0], $templates[0]];
    $unfold[0]['t'] = 'txt';
    $unfold[0]['pp'] = 'md';
    $unfold[1]['t'] = 'html';
    $unfold[1]['pp'] = 'md';
    $unfold[1]['c'] = sprintf('<!-- pp:md -->%s<!-- pp:md -->', $unfold[1]['c']);
    return $unfold;
}

function post_process($t, $opts) {
    if ($t['t'] == 'html' && $t['pp'] == 'md') {
        $parts = explode('<!-- pp:md -->', $t['res']);
        if ($parts[2]) {
            $parts[1] = markdown($parts[1], $opts['markdown']);
            $t['res'] = join("\n", $parts);
        }
    }
    return $t;
}

function markdown($md, $parser) {
    return $parser->text($md);
}

function array_blocklist($arr, $block) {
    if (is_string($block)) {
        $block = explode(' ', $block);
    }
    return array_diff_key($arr, array_flip($block));
}

function load_template($base, $name, $type) {
    $fname = join('/', [$base, $name . '.' . $type]);
    $exists = file_exists($fname);
    return [
        'n' => $name,
        't' => $type,
        'b' => $base,
        'f' => $fname,
        'x' => $exists,
        'z' => $exists ? file_get_contents($fname) : ''
    ];
}

function load_helper($opts) {
    return include __DIR__ . '/hb_helper.php';
}

function parse_yaml($content, $parser) {
    $document = $parser->parse($content, false);
    return [
        'd' => $document->getYAML() ?? [],
        'c' => $document->getContent() ?? ''
    ];
}

function name_layout($layout) {
    return $layout ? '__' . $layout : null;
}

function pick_layout(...$datalists) {
    $layout = null;

    foreach ($datalists as $l) {
        if (isset($l['layout'])) {
            $layout = $l['layout'];
            break;
        }
    }
    return $layout ? '__' . $layout : null;
}

function add_layout_tag($base, $src, $layout, $type) {
    if ($layout && file_exists($base . '/' . $layout . '.' . $type)) {
        return sprintf("{{#> %s }}\n%s\n{{/ %s }}", $layout, $src, $layout);
    } else {
        return $src;
    }
}

// name is the name that is used in the handlebars template
function load_partial($base, $name, $type) {
    $fname = "$base/{$name}.{$type}";
    if (file_exists($fname)) {
        return file_get_contents($fname);
    }
    return "[partial (file:$fname) not found]";
}

function compile($src, $ctx, $helper = []) {
    return LightnCandy::compile(
        $src,
        [
            'partialresolver' => function ($cx, $name) use ($ctx) {
                return load_partial($ctx['b'], $name, $ctx['t']);
            },
            'helpers' => array_merge([
                'embed' => function ($context, $options) use ($processor) {
                    // im compile step nix tun,
                    // erst im runstep wird die embedliste produziert
                    return $context;
                },
            ], $helper),
            'flags' => LightnCandy::FLAG_ERROR_LOG | LightnCandy::FLAG_RUNTIMEPARTIAL | LightnCandy::FLAG_ADVARNAME
        ]
    );
}

function run($code, $data, $ctx) {
    $embeds = [];
    $renderer = eval($code);

    $res = $renderer($data, [
        'helpers' => [
            'embed' => function ($context, $options) use (&$embeds, $ctx) {
                $file = $ctx['b'] . '/' . $context;
                $hash = 'embed-' . md5($file) . '-embed';
                $embeds[$hash] = $file;
                dbg('++ embed runtime', $context, $hash, $file);
                return $hash;
            }
        ],
    ]);
    return ['res' => $res, 'embeds' => $embeds];
}

function get_data($key) {
    return $this->tfiles['txt']['d'][$key];
}

function process_string($str, $data, $helper = []) {
    $r = eval(LightnCandy::compile($str, [
        'helpers' => $helper,
        'flags' => LightnCandy::FLAG_ERROR_LOG | LightnCandy::FLAG_RUNTIMEPARTIAL | LightnCandy::FLAG_ADVARNAME
    ]));
    return $r($data);
}
