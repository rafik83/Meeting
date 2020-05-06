<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Sheet;

use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Participant\CardListViewQuery;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;

class ChangeParticipantOrderAction
{
    /** @var EngineInterface */
    private $engine;

    /** @var QueryBusInterface */
    private $queryBus;

    public function __construct
    (
        EngineInterface $engine,
        QueryBusInterface $queryBus
    )
    {
        $this->engine = $engine;
        $this->queryBus = $queryBus;
    }

    public function __invoke(
        Sheet $sheet,
        UserDomain $userDomain,
        string $locale,
        string $key
    )
    {
        $cardListViewQuery = new CardListViewQuery($sheet, $userDomain->getUser(), $locale, false);
        $participants      = $this->queryBus->handle($cardListViewQuery);
        $event = $sheet->getEvent();

        return new Response(
            $this->engine->render(
                '@Event/Sheet/changeOrderParticipant.html.twig',
                [
                    'participants' => $participants,
                    'event' => $event
                ]
            )
        );
    }
}
