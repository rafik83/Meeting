<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\Happening;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Exception\Happening\EmptyHappeningParticipationException;
use Proximum\Vimeet\Application\Query\Happening\Admin\HappeningParticipantViewQuery;
use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Application\View\Happening\Admin\HappeningParticipantListView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\HttpFoundation\Response\CsvFileResponse;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Happening\PartcipantAction;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ExportActionTest extends TestCase
{
    /** @var ObjectProphecy */
    private $authorizationCheckerAdapter;

    /** @var ObjectProphecy */
    private $flashBag;

    /** @var ObjectProphecy */
    private $router;

    /** @var ObjectProphecy */
    private $queryBus;

    /** @var ObjectProphecy */
    private $serializerAdapter;

    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $request;

    public function setUp()
    {
        $this->authorizationCheckerAdapter = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $this->flashBag = $this->prophesize(FlashBagInterface::class);
        $this->router = $this->prophesize(RouterInterface::class);
        $this->queryBus = $this->prophesize(QueryBusInterface::class);
        $this->serializerAdapter = $this->prophesize(SerializerAdapterInterface::class);
        $this->event = $this->prophesize(Event::class);
        $this->request = $this->prophesize(Request::class);
    }

    public function testAccessDenied()
    {
        $this->expectException(AccessDeniedException::class);

        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $action = new PartcipantAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal(),
            $this->queryBus->reveal(),
            $this->serializerAdapter->reveal()
        );

        $action($this->request->reveal(), $this->event->reveal());
    }

    public function testEmptyHappeningParticipationException()
    {
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->request->getLocale()->willReturn('de');
        $this->event->getAvailableLocale('de')->willReturn('fr');
        $this->event->getId()->willReturn(12);

        $this->queryBus
            ->handle(new HappeningParticipantViewQuery($this->event->reveal(), 'fr'))
            ->shouldBeCalled()
            ->willThrow(new EmptyHappeningParticipationException())
        ;

        $this->flashBag->add('error', 'flash.admin.happening.participation.empty')->shouldBeCalled();

        $this->router->generate('admin_happening_list', ['event' => 12])->shouldBeCalled()->willReturn('/route');

        $action = new PartcipantAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal(),
            $this->queryBus->reveal(),
            $this->serializerAdapter->reveal()
        );

        $result = $action($this->request->reveal(), $this->event->reveal());

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertEquals('/route', $result->getTargetUrl());
    }

    public function testExport()
    {
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->request->getLocale()->willReturn('de');
        $this->event->getAvailableLocale('de')->willReturn('fr');
        $this->event->getId()->willReturn(12);

        $view = $this->prophesize(HappeningParticipantListView::class);
        $this->queryBus
            ->handle(new HappeningParticipantViewQuery($this->event->reveal(), 'fr'))
            ->shouldBeCalled()
            ->willReturn($view->reveal())
        ;

        $this->serializerAdapter->serialize(
            $view->reveal(),
            'csv',
            [
                'locale'        => 'fr',
                'charset'       => Charset::WINDOWS_1252,
                'csv_delimiter' => ';',
            ]
            )->shouldBeCalled()
            ->willReturn('serialize_content');

        $this->flashBag->add(Argument::any())->shouldNotBeCalled();
        $this->router->generate(Argument::any())->shouldNotBeCalled();

        $action = new PartcipantAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal(),
            $this->queryBus->reveal(),
            $this->serializerAdapter->reveal()
        );

        $result = $action($this->request->reveal(), $this->event->reveal());

        $this->assertInstanceOf(CsvFileResponse::class, $result);
    }
}
