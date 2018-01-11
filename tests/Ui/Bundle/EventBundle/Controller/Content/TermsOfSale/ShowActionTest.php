<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Ui\Bundle\EventBundle\Controller\Content\TermsOfSale;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Content\TermsOfSaleViewQuery;
use Proximum\Vimeet\Domain\View\Content\TermsOfSaleView;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Content\TermsOfSale\ShowAction;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

class ShowActionTest extends TestCase
{
    public function testInvoke()
    {
        $sheet = SheetFactory::create();
        $event = EventFactory::createEvent();

        $locale      = 'fr';
        $content     = 'foobar';
        $response    = $this->prophesize(Response::class);
        $request     = $this->prophesize(Request::class);
        $eventDomain = $this->prophesize(EventDomain::class);
        $query       = new TermsOfSaleViewQuery($event, $locale);
        $view        = new TermsOfSaleView($content);
        $engine      = $this->prophesize(EngineInterface::class);

        $eventDomain->getEvent()->shouldBeCalled()->willReturn($event);
        $request->getLocale()->shouldBeCalled()->willReturn($locale);

        $engine->renderResponse('EventBundle:Content:terms-of-sale.html.twig', [
            'sheet'   => $sheet,
            'event'   => $event,
            'content' => 'foobar'
        ])->shouldBeCalled()->willReturn($response->reveal());

        $queryBus = $this->prophesize(QueryBusInterface::class);
        $queryBus->handle($query)->shouldBeCalled()->willReturn($view);

        $action   = new ShowAction($engine->reveal(), $queryBus->reveal());
        $response = $action($request->reveal(), $sheet, $eventDomain->reveal());

        $this->assertInstanceOf(Response::class, $response);
    }
}
