<?php

declare(strict_types=1);

error_reporting(\E_ALL);

use PHPUnit\Framework\TestCase;
use function emil\template\load_helper;
use function emil\template\process;
use function emil\template\process_string;

final class EmailTest extends TestCase {
    public $opts;
    public $mailer;

    public function setUp(): void {
        $opts = [
            'base' => __DIR__ . '/data',
            'frontparser' => new \Mni\FrontYAML\Parser,
            'markdown' => new \Parsedown(),
            'types' => ['md', 'txt', 'html']
        ];
        $opts['helper'] = load_helper($opts);
        $this->opts = $opts;

        // dbg('++ md test', $template, $opts['helper']['markdown']('**hi**'));

        // [$views, $data] = process($template, $data, $opts);
        $this->mailer = new emil\mailer(['transport' => 'null://null']);
    }

    public function testBasicEmail(): void {
        $data = ['name' => 'otto', 'what' => 'fun', 'from' => '"Support" office@acme.com'];
        [$views, $data] = process('t1', $data, $this->opts);
        $data['subject'] = process_string($data['subject'], $data, $this->opts['helper']);

        $email = $this->mailer->create_email([
            'txt' => $views[0]['res'],
            'html' => $views[1]['res'],
            'embeds' => $views[1]['embeds']
        ], $data);

        $mime = $email->toString();
        $this->assertStringContainsString('Content-Type: text/plain', $mime);
        $this->assertStringContainsString('Subject: Welcome otto', $mime);
        $this->assertStringContainsString('From: Support <office@acme.com>', $mime);
        $this->assertStringContainsString("let's have fun", $mime);
    }

    public function xtestBasicTemplate(): void {
        $data = ['name' => 'otto', 'what' => 'fun'];
        [$views, $data] = process('t1', $data, $this->opts);
        $this->assertCount(2, $views);
        // no html version
        $this->assertEmpty($views[1]['res']);
        $this->assertStringContainsString('have fun', $views[0]['res']);
        // dd($views);
    }
}
