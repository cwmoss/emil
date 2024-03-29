<?php

use Ahc\Jwt\JWT;
use Ahc\Jwt\JWTException;

function resp($data) {
    header('Content-Type: application/json'); //; charset=utf-8
    print json_encode($data);

    dbg('+++ finished');
}

function e404($msg = 'not found') {
    header('HTTP/1.1 404 Not Found');
    resp(['fail' => $msg]);
}

function e401($msg = 'unauthorized api request') {
    dbg('+++ 401 +++ ');
    header('HTTP/1.1 401 Unauthorized');
    resp(['fail' => $msg]);
    exit;
}

if (!function_exists('d')) {
    function d(...$args) {
        echo '<pre>';
        foreach ($args as $arg) {
            print_r($arg);
        }
        echo '</pre>';
    }
}

if (!function_exists('dd')) {
    function dd(...$args) {
        d(...$args);
        die;
    }
}

function dbg($txt, ...$vars) {
    // im servermodus wird der zeitstempel automatisch gesetzt
    //	$log = [date('Y-m-d H:i:s')];
    $log = [];
    if (!is_string($txt)) {
        array_unshift($vars, $txt);
    } else {
        $log[] = $txt;
    }
    $log[] = join(' ', array_map('json_encode', $vars));
    error_log(join(' ', $log));
}

function get_json_and_raw_req() {
    $raw = get_raw_req();
    $post = json_decode($raw, true);
    return [$post, $raw];
}

function get_json_req() {
    return json_decode(get_raw_req(), true);
}

function get_raw_req() {
    dbg('++++ raw input read ++++');
    return file_get_contents('php://input');
}

function get_req_headers($router) {
    dbg('+++ router get headers');
    return array_change_key_case($router->getRequestHeaders());
}

function url_to_pdo_dsn($url) {
    $parts = parse_url($url);

    return [
        $parts['scheme'] . ':host=' . $parts['host'] . ';dbname=' . trim($parts['path'], '/'),
        $parts['user'],
        $parts['pass']
    ];
}

function gen_secret($bytes = 32) {
    return rtrim(strtr(base64_encode(random_bytes($bytes)), '+/', '-_'), '=');
}

function gen_secret_hex($bytes = 32) {
    return bin2hex(random_bytes($bytes));
}

function gen_password($len = 15) {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz' .
        '0123456789-!?@#$%#';

    $str = '';
    $max = strlen($chars) - 1;

    for ($i = 0; $i < $len; $i++) {
        $str .= $chars[random_int(0, $max)];
    }

    return $str;
}

function gen_jwt_secret() {
    return gen_secret(64);
}

function gen_jwt($secret, $org) {
    $token = (new JWT($secret, 'HS256', 1800))->encode(['org' => $org, 'scopes' => ['user']]);
    return $token;
}

function check_jwt($secret, $token) {
    // TODO: let it crash
    if (!$secret || !$token) {
        return false;
    }

    try {
        $payload = (new JWT($secret, 'HS256', 1800))->decode($token);
    } catch (JWTException $e) {
        $payload = false;
    }
    return $payload;
}

/*
password_verify ( string $password , string $hash )
*/
function array_blocklist($arr, $block) {
    if (is_string($block)) {
        $block = explode(' ', $block);
    }
    return array_diff_key($arr, array_flip($block));
}
function array_search_fun($needle_fun, $haystack) {
    foreach ($haystack as $k => $v) {
        if (call_user_func($needle_fun, $v) === true) {
            return [$k, $v];
        }
    }
    return null;
}

function array_delete($array, $idx) {
    array_splice($array, $idx, 1);
    return $array;
}

function normalize_files_array($files = []) {
    $normalized_array = [];

    foreach ($files as $index => $file) {
        if (!is_array($file['name'])) {
            $normalized_array[$index][] = $file;
            continue;
        }

        foreach ($file['name'] as $idx => $name) {
            $normalized_array[$index][$idx] = [
                'name' => $name,
                'type' => $file['type'][$idx],
                'tmp_name' => $file['tmp_name'][$idx],
                'error' => $file['error'][$idx],
                'size' => $file['size'][$idx]
            ];
        }
    }

    return $normalized_array;
}

function stream_to_file($name) {
    $tmpfname = tempnam(sys_get_temp_dir(), 'emil-');
    file_put_contents($tmpfname, file_get_contents('php://input'));
    return [
        'name' => $name,
        'type' => 'stream',
        'tmp_name' => $tmpfname,
        'error' => 0,
        'size' => filesize($tmpfname)
    ];
}

function send_file($base, $file) {
    $file = basename($file);
    if (preg_match('/css$/', $file)) {
        header('Content-Type: text/css');
    } elseif (preg_match('/js$/', $file)) {
        header('Content-Type: text/javascript');
    } elseif (preg_match('/svg$/', $file)) {
        header('Content-Type: image/svg+xml');
    } elseif (preg_match('/html$/', $file)) {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        header('Content-Type: text/html');
    }
    dbg('sending', $base . '/ui/' . $file);
    readfile($base . '/ui/' . $file);
}

function get_trace_from_exception($e) {
    $class = get_class($e);
    $pclass = get_parent_class($e);
    $m = $e->getMessage();

    $fm = sprintf(
        "%s:\n   %s line: %s code: %s\n   via %s%s\n",
        $m,
        $e->getFile(),
        $e->getLine(),
        $e->getCode(),
        $class,
        $pclass ? ', ' . $pclass : ''
    );
    $trace .= $fm . $e->getTraceAsString();
    return $trace;
}

function check_admin($hdrs, $server) {
    dbg('+++ check admin ', $hdrs, getallheaders());
    return check_auth(['admin', 'x-emil-admin', 'EMIL_ADMIN_KEY'], $hdrs, $server);
}

function check_api($hdrs, $server, $etc_org) {
    $server['ORG_PWD'] = array_map(fn ($k) => $k['key'], $etc_org['api_keys']);
    return check_auth(['api', 'x-emil-api', 'ORG_PWD'], $hdrs, $server);
}

function check_auth($key_names, $hdrs, $server) {
    list($basicuser, $xheader, $envsecret) = $key_names;
    if (!$server[$envsecret]) {
        return false;
    }
    $secrets = $server[$envsecret];

    // first check for auth xheader
    // then for basic-auth header basicuser
    if (isset($hdrs[$xheader])) {
        return equal_or_in_array($hdrs[$xheader], $secrets);
    } else {
        // php has a shortcut here
        // _SERVER:  PHP_AUTH_USER":"robert","PHP_AUTH_PW":"seeeeecret"
        return ($server['PHP_AUTH_USER'] === $basicuser && equal_or_in_array($server['PHP_AUTH_PW'], $secrets));
    }
    return false;
}

function equal_or_in_array($needle, $haystack) {
    return (is_array($haystack) && in_array($needle, $haystack)) ||
        (!is_array($haystack) && $needle == $haystack);
}

function org_options_read($base, $name) {
    $cont = file_get_contents("{$base}/{$name}.json");
    if (!$cont) {
        return [];
    }
    $data = json_decode($cont, true);
    return $data ?: [];
}

function org_options_save($base, $name, $data) {
    return file_put_contents("{$base}/{$name}.json", json_encode($data));
}

function org_options_update($base, $name, $data) {
    return org_options_save($base, $name, array_merge(
        org_options_read($base, $name),
        $data
    ));
}
