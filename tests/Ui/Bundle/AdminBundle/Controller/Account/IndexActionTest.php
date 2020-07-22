<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\Account;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Account\IndexAction;
use Symfony\Component\Templating\EngineInterface;

class IndexActionTest extends TestCase
{
    public function testInvoke(): void
    {
        $engine = $this->prophesize(EngineInterface::class);
        $engine->render('AdminBundle:Account:index.html.twig')->shouldBeCalled()->willReturn('<html></html>');
        $action = new IndexAction($engine->reveal());
        $result = $action();

        $this->assertEquals('<html></html>', $result->getContent());
    }
}
