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
    public $base;

    public function __construct(\emil\mailer $mailer, $frontparser, $base) {
        $this->mailer = $mailer;
        $this->frontparser = $frontparser;
        $this->base = $base;
    }

    // xorc\mailer::send('register', ['to'=>$this->registration->email], ['u'=>$this->registration]);

    public function send($template, $data) {
        $opts = [
            'base' => $this->base,
            'frontparser' => $this->frontparser,
            'markdown' => new \Parsedown(),
            'types' => ['md', 'txt', 'html']
        ];
        $opts['helper'] = load_helper($opts);

        [$views, $data] = process($template, $data, $opts);

        $data['subject'] = process_string($data['subject'], $data, $opts['helper']);
        dbg('++ data', $data);

        try {
            $this->mailer->send([
                'txt' => $views[0]['res'],
                'html' => $views[1]['res'],
                'embeds' => $views[1]['embeds']
            ], $data);
        } catch (\throwable $e) {
            return ['err' => get_trace_from_exception($e)];
        }

        return ['res' => 'ok sent'];
    }

    public function sendx($template, $data, $hdrs) {
        dbg('++ send data', $data);

        $views = $this->processor->process($template, $data);

        $subject = $this->processor->get_data('subject') ?? $data['subject'];
        $subject = $this->processor->process_string($subject, $data);
        $data['subject'] = $subject;

        try {
            $this->mailer->send($views, $data);
        } catch (\throwable $e) {
            return ['err' => get_trace_from_exception($e)];
        }

        return ['res' => 'ok sent'];
    }
}
