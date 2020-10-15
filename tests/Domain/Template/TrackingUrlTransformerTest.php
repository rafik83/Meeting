<?php

namespace Proximum\Vimeet\Tests\Domain\Template;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\TemplateObject\Url;
use Proximum\Vimeet\Domain\Template\TrackingUrlTransformer;
use Symfony\Component\Routing\RequestContext;

class TrackingUrlTransformerTest extends TestCase
{
    public function testRequestContextHasLocale(): void
    {
        $event = $this->prophesize(Event::class);
        $sheet = $this->prophesize(Sheet::class);
        $object = $this->prophesize(Url::class);

        $sheet->getId()->willReturn(1);
        $sheet->getEvent()->shouldBeCalled()->willReturn($event);
        $object->getUid()->willReturn(2);

        $router = $this->prophesize(RouterInterface::class);

        $context = $this->prophesize(RequestContext::class);
        $context->getParameter('_locale')->willReturn('fr');
        $router->getContext()->shouldBeCalled()->willReturn($context->reveal());
        $eventUrlGenerator = $this->prophesize(Event\EventUrlGeneratorInterface::class);

        $eventUrlGenerator->generateEventAbsoluteUrl(
            $event->reveal(),
            'event_catalog_sheet_follow_link',
            ['sheet' => 1, 'objectId' => 2, '_locale'=>'fr']
        )->shouldBeCalled()->willReturn('http://example.org');

        $transformer = new TrackingUrlTransformer(
            $router->reveal(),
            $eventUrlGenerator->reveal()
        );
        $url = $transformer->transform($sheet->reveal(), $object->reveal());
        $this->assertEquals('http://example.org', $url);
    }

    public function testRequestContextHasntLocale(): void
    {
        $event = $this->prophesize(Event::class);
        $sheet = $this->prophesize(Sheet::class);
        $object = $this->prophesize(Url::class);

        $event->getLocaleFallback()->willReturn('cn');
        $sheet->getId()->willReturn(1);
        $sheet->getEvent()->willReturn($event->reveal());
        $object->getUid()->willReturn(2);

        $requestContext = $this->prophesize(RequestContext::class);
        $requestContext->getParameter('_locale')->willReturn(null);

        $router = $this->prophesize(RouterInterface::class);
        $eventUrlGenerator = $this->prophesize(Event\EventUrlGeneratorInterface::class);

        $router->getContext()->willReturn($requestContext->reveal());
        $eventUrlGenerator->generateEventAbsoluteUrl(
            $event->reveal(),
            'event_catalog_sheet_follow_link',
            ['sheet' => 1, 'objectId' => 2, '_locale' => 'cn']
        )->shouldBeCalled()->willReturn('http://example.org');

        $transformer = new TrackingUrlTransformer($router->reveal(), $eventUrlGenerator->reveal());
        $url = $transformer->transform($sheet->reveal(), $object->reveal());
        $this->assertEquals('http://example.org', $url);
    }
}
