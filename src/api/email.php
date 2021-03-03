<?php
namespace api;

use twentyseconds\template\processor;

class email{
    
    public $mailer;
    public $processor;
    
    function __construct($mailer, $processor){
        $this->mailer = $mailer;
        $this->processor = $processor;
    }

// xorc\mailer::send('register', ['to'=>$this->registration->email], ['u'=>$this->registration]);

    function send($template, $data, $hdrs){
        $views = $this->processor->process($template, $data);

        $subject = $this->processor->get_data("subject")??$data['subject'];
        $subject = $this->processor->process_string($subject, $data);
        $data['subject'] = $subject;
        
        $this->mailer->send($views, $data);
        
        return ['res'=>'ok sent'];
    }
    
    function sendx($type, $doc, $hdrs){
        $doc['_type'] = $type;
        $id = $this->store->insert_doc($doc);
        if($id){
            $this->store->log($id, $_SERVER['REMOTE_ADDR'], $hdrs);
        }
        return ['res'=>'ok'];
    }
}
