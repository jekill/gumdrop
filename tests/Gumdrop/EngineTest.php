<?php
namespace Gumdrop\Tests;

require_once __DIR__ . '/../TestCase.php';
require_once __DIR__ . '/../../Gumdrop/Engine.php';
require_once __DIR__ . '/../../Gumdrop/Configuration.php';
require_once __DIR__ . '/../../Gumdrop/PageConfiguration.php';
require_once __DIR__ . '/../../Gumdrop/PageCollection.php';
require_once __DIR__ . '/../../vendor/dflydev/markdown/src/dflydev/markdown/IMarkdownParser.php';
require_once __DIR__ . '/../../vendor/dflydev/markdown/src/dflydev/markdown/MarkdownParser.php';

class Engine extends \Gumdrop\Tests\TestCase
{
    public function testRunBehavesAsExpected()
    {
        $Page1 = \Mockery::mock('\Gumdrop\Page');
        $Page2 = \Mockery::mock('\Gumdrop\Page');
        $Page1
            ->shouldReceive('setConfiguration')
            ->with(\Mockery::type('\Gumdrop\PageConfiguration'))
            ->globally()
            ->ordered()
            ->once();
        $Page1
            ->shouldReceive('convertMarkdownToHtml')
            ->globally()
            ->ordered()
            ->once();
        $Page1
            ->shouldReceive('setCollection')
            ->globally()
            ->ordered()
            ->once()
            ->andReturn('collection1');
        $Page2
            ->shouldReceive('setConfiguration')
            ->with(\Mockery::type('\Gumdrop\PageConfiguration'))
            ->globally()
            ->ordered()
            ->once();
        $Page2
            ->shouldReceive('convertMarkdownToHtml')
            ->globally()
            ->ordered()
            ->once();
        $Page2
            ->shouldReceive('setCollection')
            ->globally()
            ->ordered()
            ->once()
            ->andReturn('collection2');
        $Page1
            ->shouldReceive('applyTwigLayout')
            ->globally()
            ->ordered()
            ->once();
        $Page1
            ->shouldReceive('writeHtmFiles')
            ->with('destination')
            ->globally()
            ->ordered()
            ->once();
        $Page2
            ->shouldReceive('applyTwigLayout')
            ->globally()
            ->ordered()
            ->once();
        $Page2
            ->shouldReceive('writeHtmFiles')
            ->globally()
            ->ordered()
            ->with('destination')
            ->once();
        $PageCollection = new \Gumdrop\PageCollection(array(
            $Page1,
            $Page2
        ));

        $Engine = new \Gumdrop\Engine($this->getApp());
        $Engine->run($PageCollection, 'destination');
    }
}