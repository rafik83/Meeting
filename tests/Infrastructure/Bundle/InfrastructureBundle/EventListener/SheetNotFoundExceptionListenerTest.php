<?php

namespace Proximum\Vimeet\tests\Infrastructure\Bundle\InfrastructureBundle\EventListener;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Exception\Sheet\SheetNotFoundException;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\SheetNotFoundExceptionListener;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;

class SheetNotFoundExceptionListenerTest extends TestCase
{
    public function testOnKernelException()
    {
        $event   = EventFactory::createEvent();
        $request = Request::create('/undefined-sheet');
        $request->setDefaultLocale('fr');

        $request->headers->set('HOST', $event->getDomain());

        $router = $this->prophesize(RouterInterface::class);
        $router->generate('event', ['_locale' => 'fr'])->shouldBeCalled()->willReturn($event->getDomain());

        $eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $eventRepository->getEventByDomain($event->getDomain())->shouldBeCalled()->willReturn($event);

        $kernelEvent = new ExceptionEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $request,
            HttpKernelInterface::MASTER_REQUEST,
            new SheetNotFoundException()
        );

        $listener = new SheetNotFoundExceptionListener(
            $router->reveal(),
            $eventRepository->reveal(),
            'admin.vimeet.proximum.dev'
        );
        $listener->onKernelException($kernelEvent);

        $this->assertInstanceOf(RedirectResponse::class, $response = $kernelEvent->getResponse());
        $this->assertSame($event->getDomain(), $response->getTargetUrl());
    }

    public function testOnKernelExceptionOnlyHandlesSheetNotFoundException()
    {
        $router = $this->prophesize(RouterInterface::class);

        $router->generate()->shouldNotBeCalled();

        $eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $eventRepository->getEventByDomain()->shouldNotBeCalled();

        $kernelEvent = new ExceptionEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            Request::create('/'),
            HttpKernelInterface::MASTER_REQUEST,
            new \Exception()
        );

        $listener = new SheetNotFoundExceptionListener(
            $router->reveal(),
            $eventRepository->reveal(),
            'admin.vimeet.proximum.dev'
        );
        $listener->onKernelException($kernelEvent);

        $this->assertNotInstanceOf(RedirectResponse::class, $kernelEvent->getResponse());
    }

    public function testOnKernelExceptionReturnsIfEventCannotBeFound()
    {
        $event  = EventFactory::createEvent();
        $router = $this->prophesize(RouterInterface::class);

        $request = Request::create('/undefined-sheet');
        $request->headers->set('HOST', $event->getDomain());

        $eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $eventRepository->getEventByDomain($event->getDomain())->shouldBeCalled()->willReturn(null);

        $kernelEvent = new ExceptionEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $request,
            HttpKernelInterface::MASTER_REQUEST,
            new SheetNotFoundException()
        );

        $listener = new SheetNotFoundExceptionListener(
            $router->reveal(),
            $eventRepository->reveal(),
            'admin.vimeet.proximum.dev'
        );
        $listener->onKernelException($kernelEvent);

        $this->assertNotInstanceOf(RedirectResponse::class, $kernelEvent->getResponse());
    }
}
