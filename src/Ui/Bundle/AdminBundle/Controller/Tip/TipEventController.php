<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Tip;

use Proximum\Vimeet\Application\Query\Tip\Event\TipListViewQuery;
use Proximum\Vimeet\Application\Query\Tip\Event\TipListViewQuery as EventTipListViewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TipEventController extends Controller
{
    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return Response
     */
    public function listAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');

        $tipListViewQuery = new TipListViewQuery($event, $request->query->get('page', 1));
        $tipListViewQuery = new EventTipListViewQuery($event, $request->query->get('page', 1));

        $tipListView = $this->get('tactician.commandbus')->handle($tipListViewQuery);

        return $this->render('@Admin/Tip/Event/list.html.twig', [
            'event'       => $event,
            'tipListView' => $tipListView
        ]);
    }

    public function affectAction()
    {
        return '';
    }
}
