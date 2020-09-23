<?php

namespace Proximum\Vimeet\Tests\Domain\Template;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\TemplateObject\Url;
use Proximum\Vimeet\Domain\Template\TrackingUrlTransformer;
use Symfony\Component\Routing\RequestContext;

class TrackingUrlTransformerTest extends TestCase
{
    public function test()
    {
        $sheet = $this->prophesize(Sheet::class);
        $object = $this->prophesize(Url::class);

        $sheet->getId()->willReturn(1);
        $object->getUid()->willReturn(2);

        $router = $this->prophesize(RouterInterface::class);
        $router->generate(
            'event_catalog_sheet_follow_link',
            ['sheet' => 1, 'objectId' => 2, '_locale'=>'fr']
        )->shouldBeCalled()->willReturn('http://example.org');
        $context = $this->prophesize(RequestContext::class);
        $context->getParameter('_locale')->willReturn('fr');
        $router->getContext()->shouldBeCalled()->willReturn($context->reveal());

        $transformer = new TrackingUrlTransformer($router->reveal());
        $url = $transformer->transform($sheet->reveal(), $object->reveal());
        $this->assertEquals('http://example.org', $url);
    }
}
