<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\Account;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Account\IndexAction;
use Twig\Environment;

class IndexActionTest extends TestCase
{
    public function testInvoke(): void
    {
        $twig = $this->prophesize(Environment::class);
        $twig->render('AdminBundle:Account:index.html.twig')->shouldBeCalled()->willReturn('<html></html>');
        $action = new IndexAction($twig->reveal());
        $result = $action();

        $this->assertEquals('<html></html>', $result->getContent());
    }
}
