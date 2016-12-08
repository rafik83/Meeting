<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Query\Happening\HappeningViewQuery;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AgendaController extends Controller
{
    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     *
     * @return Response
     */
    public function indexAction(Request $request, EventDomain $eventDomain)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $happeningListView = $this->get('tactician.commandbus.query')->handle(
            new HappeningViewQuery($eventDomain->getEvent(), $request->getLocale())
        );

        return $this->render('EventBundle:Agenda:index.html.twig', [
            'event'         => $eventDomain->getEvent(),
            'happeningList' => $happeningListView,
        ]);
    }
}
