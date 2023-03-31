<?php

namespace emil;

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer as symailer;

use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\File;

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class mailer {
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
        // $cred = parse_url($this->conf['transport']);
        $transport = Transport::fromDsn($this->conf['transport']);
        $mailer = new symailer($transport);
        return $mailer;
    }

    public function create_email($views, $data) {
        $m = new Email();
        $hdrs = $this->header_from_data($data);
        // dd("headers", $hdrs);
        // dd($views);
        if ($views['txt']) {
            $m->text($views['txt']);
        }
        if ($views['html']) {
            if ($views['embeds']) {
                // dd('embeds:', $views['embeds']);
                foreach ($views['embeds'] as $cid => $embed) {
                    if (!file_exists($embed)) {
                        continue;
                    }
                    $m->addPart((new DataPart(new File($embed), $cid, 'image/png'))->asInline());
                }
            }
            $m->html($views['html']);
        }

        $this->set_headers($m, $hdrs);

        // $serializedEmail = serialize($email);
        return $m;
    }

    /*->from('hello@example.com')
            ->to('you@example.com')
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('Time for Symfony Mailer!')
            ->text('Sending emails is fun again!')
            ->html('<p>See Twig integration for better HTML integration!</p>');
            */


    public function send($views, $data = []) {
        $email = $this->create_email($views, $data);
        //dd($email);
        $mailer = $this->transport();

        try {
            $mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            // some error prevented the email sending; display an
            // error message or try to resend the message
            return $e->getMessage();
        }
        return true;
    }

    public function header_from_data($data) {
        return [
            'to' => $data['to'],
            'from' => $data['from'],
            'subject' => $data['subject']
        ];
    }

    /*
       aus reply-to wird Reply-To usw.
    */
    public function set_headers($message, $hdrs) {
        $addr_keys = explode(' ', 'from to reply-to errors-to cc bcc sender return-path');
        $hdrs = array_merge([], $hdrs);

        $headers = $message->getHeaders();
        //print $headers->toString();
        //print_r($hdrs);
        foreach ($hdrs as $key => $h) {
            if (!$h || !trim($h)) continue;
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
                    $message->from(...$h);
                } elseif ($key == 'to') {
                    $message->to(...$h);
                } else {
                    // TODO: make address 
                    //  Argument #2 ($address) must be of type Symfony\Component\Mime\Address|string, array given
                    // dd("hdr key val", $key, $h);
                    $headers->addMailboxHeader($hmail, ...$h);
                }
            } else {
                if ($key == 'subject') {
                    $message->subject($h);
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
        //dd("addr line", $addr_line);
        foreach (explode(',', $addr_line) as $line) {
            if (preg_match('/^(.*?)([^ ]+@[^ ]+)?$/', trim($line), $mat)) {
                $name = trim(str_replace('"', '', $mat[1]));
                $email = trim($mat[2], '<>');
                dbg("parsed address", $email, $name);
                flush();
                if ($name) {
                    $addrs[] = new Address($email, $name);
                } else {
                    $addrs[] = new Address($email);
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
