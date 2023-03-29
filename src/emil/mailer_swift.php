<?php

namespace emil;

class mailer_swift {
    public $conf;

    public function __construct($conf) {
        $this->set_conf($conf);
    }

    public function set_conf($conf = []) {
        $this->conf = $conf;
    }

    /*
       http://swiftmailer.org/docs/messages.html

       transport:
          smtp://rw@20sec.net:geheim@mail.20sec.de:25
          sendmail://localhost/usr/bin/sendmail
          php://localhost
    */
    public function transport() {
        //var_dump($this->conf);
        $cred = parse_url($this->conf['transport']);
        //var_dump($cred);
        //log_debug($cred);
        if ($cred['scheme'] == 'smtp') {
            $transport = new \Swift_SmtpTransport($cred['host'], $cred['port'] ? $cred['port'] : 25);

            if ($cred['user']) {
                $transport->setUsername($cred['user'])
                    ->setPassword($cred['pass'])
                    // ->setPort(465)
                    ->setEncryption('ssl');
            }

            if ($this->conf['ssl']) {
                $transport->setEncryption('ssl');
            }

            if ($this->conf['ssl_nocert']) {
                $transport->setStreamOptions(['ssl' => ['allow_self_signed' => true, 'verify_peer' => false]]);
            } else {
                $sopts = [];
                foreach ($this->conf as $skey => $val) {
                    if (preg_match('/^ssl_(.*)$/', $skey, $mat)) {
                        $sopts[$mat[1]] = $val;
                    }
                }
                if ($sopts) {
                    $transport->setStreamOptions(['ssl' => $sopts]);
                }
            }
        } elseif ($cred['scheme'] == 'sendmail') {
            $transport = new \Swift_SendmailTransport($cred['path'] . ' -t');
        } elseif ($cred['scheme'] == 'php') {
            /*
                  achtung! php:// gibt es nicht mehr bei neueren versionen
            */

            $transport = new \Swift_MailTransport();
        }

        $transport = new \Swift_Mailer($transport);

        //var_dump(self::$t);
        return $transport;
    }

    public function send($views, $data = []) {
        //var_dump($views); exit;
        $hdrs = $this->header_from_data($data);
        dbg('mail headers', $hdrs);

        $m = new \Swift_Message;

        foreach ($views as $type => $body) {
            if ($type == 'txt') {
                $m->addPart($body, 'text/plain');
            } elseif ($type == 'html') {
                $repl = [];
                if ($views['embeds']) {
                    foreach ($views['embeds'] as $k => $embed) {
                        if (!file_exists($embed)) {
                            continue;
                        }

                        $img = \Swift_Image::fromPath($embed);
                        $repl[$k] = 'cid:' . $img->getId();
                        $m->attach($img);
                    }
                }

                if ($repl) {
                    $body = str_replace(array_keys($repl), $repl, $body);
                }

                //foreach(self::embed() as $cid => $embed){
                //	$m->attach($embed);
                //}
                $m->addPart($body, 'text/html');
            }
        }

        $this->set_headers($m, $hdrs);
        //print "headers OK\n";
        if ($this->conf['pretend']) {
            //log_info("[mail:pretend]\n". $m->toString());
            return true;
        }

        $trans = $this->transport();
        //var_dump($trans);
        $ok = $trans->send($m, $failures);
        //var_dump($ok);
        //var_dump($trans);
        //log_debug($trans);
        if (!$ok) {
            //log_warning("[mail] failures\n". $m->getHeaders()->toString());
            //log_warning($failures);
            return $failures;
        } else {
            return true;
        }
    }

    public function header_from_data($data) {
        return [
            'to' => $data['to'],
            'from' => $data['from'],
            'subject' => $data['subject']
        ];
    }

    // $cid = $message->embed(Swift_Image::fromPath('image.png'));
    public function embed($file = null) {
        static $embeds = [];
        // clear list
        if (is_null($file)) {
            $e = $embeds;
            $embeds = [];
            return $e;
        }
        $img = \Swift_Image::fromPath(self::$conf['basepath'] . '/' . $file);
        $id = 'cid:' . $img->getId();
        $embeds[$id] = $img;
        return $id;
    }

    /*
       aus reply-to wird Reply-To usw.
    */
    public function set_headers(&$message, $hdrs) {
        $addr_keys = explode(' ', 'from to reply-to errors-to cc bcc sender return-path');
        $hdrs = array_merge([], $hdrs);

        $headers = $message->getHeaders();
        //print $headers->toString();
        //print_r($hdrs);
        foreach ($hdrs as $key => $h) {
            // $h = str_replace('#monitor', self::$vars['monitor'], $h);
            $hmail = join('-', array_map('ucfirst', explode('-', $key)));
            if (in_array($key, $addr_keys)) {
                $h = $this->addr_line_simple_parse($h);
                // kein namensanteil erlaubt
                if ($key == 'return-path') {
                    if ($h[0]) {
                        $h = $h[0];
                    } else {
                        $h = key($h);
                    }
                    $message->setReturnPath($h);
                } elseif ($key == 'from') {
                    $message->setFrom($h);
                } else {
                    $headers->addMailboxHeader($hmail, $h);
                }
            } else {
                if ($key == 'subject') {
                    $message->setSubject($h);
                } else {
                    $headers->addTextHeader($hmail, $h);
                }
            }
        }
    }

    /*
       "walter white" ww@meth.org, bounce@meth.org, dea <office@dea.gov>, chicken wings <chicks@eat-fresh.com>
    */
    public function addr_line_simple_parse($addr_line) {
        $addrs = [];
        foreach (explode(',', $addr_line) as $line) {
            if (preg_match('/^(.*?)([^ ]+@[^ ]+)?$/', trim($line), $mat)) {
                $name = trim(str_replace('"', '', $mat[1]));
                $email = trim($mat[2], '<>');
                if ($name) {
                    $addrs[$email] = $name;
                } else {
                    $addrs[] = $email;
                }
            }
        }
        return $addrs;
    }

    public static function strip_headers($txt) {
        $h = [];
        $vars = [];
        list($hdr, $body) = explode("\n\n", $txt, 2);
        if (preg_match_all("/^\s*([-\$A-z]+)\s*:(.*?)$/m", $hdr, $mat, PREG_SET_ORDER)) {
            foreach ($mat as $set) {
                $name = strtolower(trim($set[1]));
                $val = trim($set[2]);

                if ($name && $name[0] == '$') {
                    $vars[trim($name, '$')] = $val;
                } else {
                    $h[$name] = $val;
                }
            }
        }
        /*
           haben wir einen sinnvollen header gefunden?
           wir testen das 1) auf $vars 2) erstes element
        */
        if ($vars) {
            return [$body, $h, $vars];
        }
        if ($h) {
            if (in_array(key($h), explode(' ', 'subject from to reply_to cc bcc errors_to return_path'))) {
                return [$body, $h, $vars];
            }
        }
        return [$txt, [], []];
    }
}
