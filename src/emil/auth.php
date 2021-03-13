<?php
namespace emil;

class auth
{
    public function __construct($env)
    {
        $this->env = $env;
    }

    public function is_authorized_admin($hdrs)
    {
        if (check_admin($hdrs, $this->env)) {
            return true;
        }
        if ($this->check_jwt('admin')) {
            return true;
        }
       
        e401();
    }

    public function is_authorized($hdrs, $user, $etc)
    {
        if (check_api($hdrs, $this->env, $etc, $user)) {
            return true;
        }

        if ($this->check_jwt($user)) {
            return true;
        }
        
        // e401();
        return $this->is_authorized_admin($hdrs);
    }

    public function login($post)
    {
        dbg("login data", $post, $this->env);
        $ok = password_verify($post['password'], $this->env['EMIL_ADMIN_PWD']);
        if ($ok) {
            $this->set_cookie("admin");
            return ['ok'=>'logged in'];
        }
        return ['err'=>'login failed'];
    }

    public function set_cookie($user)
    {
        $secure = false;
        $domain = "";
        $path = "/";
        $cookieopts = ['expires'=>0, 'path'=>$path, 'domain'=>$domain,
            'secure'=>$secure, 'httponly'=>true, 'samesite'=>'Strict'];
        $jwt = gen_jwt($this->env['EMIL_JWT_SECRET'], $user);
        setcookie('emil', $jwt, $cookieopts);
    }
    
    public function check_jwt($user)
    {
        $sec = $this->env['EMIL_JWT_SECRET'];
        if (!$sec) {
            return false;
        }
        $token = check_jwt($sec, $_COOKIE['emil']);
        if ($token && $token['org']===$user) {
            return true;
        }
        return false;
    }
}
