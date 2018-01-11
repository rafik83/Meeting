<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Content\TermsOfSale;

use Proximum\Vimeet\Application\Query\Content\TermsOfSaleViewQuery;
use Proximum\Vimeet\Domain\Model\Sheet;
use League\Tactician\CommandBus;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

class ShowAction
{
    /** @var EngineInterface */
    private $engine;

    /** @var CommandBus */
    private $commandBus;

    /**
     * @param EngineInterface $engine
     * @param CommandBus      $commandBus
     */
    public function __construct(EngineInterface $engine, CommandBus $commandBus)
    {
        $this->engine     = $engine;
        $this->commandBus = $commandBus;
    }

    /**
     * @param Request     $request
     * @param Sheet       $sheet
     * @param EventDomain $eventDomain
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function __invoke(Request $request, Sheet $sheet, EventDomain $eventDomain)
    {
        $termsOfSaleView = $this->commandBus->handle(
            new TermsOfSaleViewQuery($eventDomain->getEvent(), $request->getLocale())
        );

        return $this->engine->renderResponse('EventBundle:Package:terms-of-sale.html.twig', [
            'sheet'   => $sheet,
            'event'   => $eventDomain->getEvent(),
            'content' => $termsOfSaleView->content
        ]);
    }
}
