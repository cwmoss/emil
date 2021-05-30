<?php

namespace api;

use function emil\template\process;
use function emil\template\process_string;
use function emil\template\load_helper;
use function emil\template\get_data;

class email {
    public $mailer;
    public $processor;
    public $frontparser;
    public $org;

    public function __construct(\emil\org $org, \emil\mailer $mailer, $frontparser) {
        $this->mailer = $mailer;
        $this->frontparser = $frontparser;
        $this->org = $org;
    }

    // xorc\mailer::send('register', ['to'=>$this->registration->email], ['u'=>$this->registration]);

    public function _send_gql($template, $rec, $data = []) {
        dbg('++ send GQL', $template);
        $rec['to'] = $rec['email'];
        unset($rec['email']);
        $data = $data ?: [];
        $data = array_merge($data, $rec);

        $rsp = $this->send($template, $data);
        return [
            'summary' => ['sent' => 1, 'failed' => 0],
            'details' => [[
                'email' => $data['to'], //$rec, // $rec['email'],
                'status' => 'ok',
                'msg' => ''
            ]]
        ];
    }

    public function send($template, $data) {
        // TODO: etc/data
        $orgdata = $this->org->preferences();
        $data = array_merge($orgdata, $data);

        //var_dump($orgdata);
        $this->mailer->conf['transport'] = $orgdata['transport'];
        //var_dump($this->mailer);
        $opts = [
            'base' => $this->org->orgbase,
            'frontparser' => $this->frontparser,
            'markdown' => new \Parsedown(),
            'types' => ['md', 'txt', 'html']
        ];
        $opts['helper'] = load_helper($opts);

        dbg('++ md test', $template, $opts['helper']['markdown']('**hi**'));

        [$views, $data] = process($template, $data, $opts);

        dbg('++ before subject', $data['subject']);

        $data['subject'] = process_string($data['subject'], $data, $opts['helper']);
        dbg('++ data', $data);

        try {
            $this->mailer->send([
                'txt' => $views[0]['res'],
                'html' => $views[1]['res'],
                'embeds' => $views[1]['embeds']
            ], $data);
        } catch (\throwable $e) {
            $trace = get_trace_from_exception($e);
            dbg('++err++', $trace);
            return ['err' => $trace];
        }

        return ['res' => 'ok sent'];
    }
}
