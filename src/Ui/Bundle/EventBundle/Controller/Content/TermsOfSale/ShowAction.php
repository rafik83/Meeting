<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Content\TermsOfSale;

use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Content\TermsOfSaleViewQuery;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;

class ShowAction
{
    /** @var EngineInterface */
    private $engine;

    /** @var QueryBusInterface */
    private $queryBus;

    /**
     * @param EngineInterface   $engine
     * @param QueryBusInterface $queryBus
     */
    public function __construct(EngineInterface $engine, QueryBusInterface $queryBus)
    {
        $this->engine   = $engine;
        $this->queryBus = $queryBus;
    }

    /**
     * @param Request     $request
     * @param Sheet       $sheet
     * @param EventDomain $eventDomain
     *
     * @return Response
     */
    public function __invoke(Request $request, Sheet $sheet, EventDomain $eventDomain): Response
    {
        $termsOfSaleView = $this->queryBus->handle(
            new TermsOfSaleViewQuery($eventDomain->getEvent(), $request->getLocale())
        );

        return $this->engine->renderResponse('EventBundle:Content:terms-of-sale.html.twig', [
            'sheet'   => $sheet,
            'event'   => $eventDomain->getEvent(),
            'content' => $termsOfSaleView->content
        ]);
    }
}
