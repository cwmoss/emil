<?php
namespace api;

use function emil\template\{process, process_string, get_data};


class email{
    
    public $mailer;
    public $processor;
    public $frontparser;
    public $base;
    
    function __construct(\emil\mailer $mailer, $frontparser, $base){
        $this->mailer = $mailer;
        $this->frontparser = $frontparser;
        $this->base = $base;
    }

// xorc\mailer::send('register', ['to'=>$this->registration->email], ['u'=>$this->registration]);

    function send($template, $data){
        [$views, $data] = process($template, $data, [
            'base'=> $this->base,
            'frontparser' => $this->frontparser,
            'types' => ['txt', 'html']
            ]);
        
        $data['subject'] = process_string($data['subject'], $data);
        dbg("++ data", $data);

        try{
            $this->mailer->send([
                'txt' => $views[0]['res'],
                'html' => $views[1]['res'],
                'embeds' => $views[1]['embeds']
                ], $data);
                
        }catch(\throwable $e){
            
            return ['err'=>get_trace_from_exception($e)];
        }
        
        return ['res'=>'ok sent'];
    }
    
    function sendx($template, $data, $hdrs){
        dbg("++ send data", $data);
        
        $views = $this->processor->process($template, $data);

        $subject = $this->processor->get_data("subject")??$data['subject'];
        $subject = $this->processor->process_string($subject, $data);
        $data['subject'] = $subject;
        
        try{
            $this->mailer->send($views, $data);
        }catch(\throwable $e){
            
            return ['err'=>get_trace_from_exception($e)];
        }
        
        
        return ['res'=>'ok sent'];
    }
    
}
