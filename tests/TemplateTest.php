<?php

declare(strict_types=1);

error_reporting(\E_ALL);

use PHPUnit\Framework\TestCase;
use function emil\template\load_helper;
use function emil\template\process;
use function emil\template\process_string;

final class TemplateTest extends TestCase {
    public $opts;

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
    }

    public function testBasicHelper(): void {
        $out = $this->opts['helper']['markdown']('**hi**');
        $this->assertEquals('<p><strong>hi</strong></p>', $out);
    }

    public function testBasicTemplate(): void {
        $data = ['name' => 'otto', 'what' => 'fun'];
        [$views, $data] = process('t1', $data, $this->opts);
        $this->assertCount(2, $views);
        // no html version
        $this->assertEmpty($views[1]['res']);
        $this->assertStringContainsString('have fun', $views[0]['res']);
        // dd($views);
    }

    public function testStringProcess(): void {
        $data = ['name' => 'otto', 'what' => 'fun', 'subject' => 'hello {{name}}'];
        $subject = process_string($data['subject'], $data, $this->opts['helper']);
        $this->assertEquals('hello otto', $subject);
    }
}
