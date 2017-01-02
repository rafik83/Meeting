<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Agenda;

use Proximum\Vimeet\Application\Query\Agenda\SheetListViewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AgendaController extends Controller
{
    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return Response|JsonResponse
     */
    public function indexAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $sheets = [];

        if ($request->isXmlHttpRequest()) {
            $query = new SheetListViewQuery($event, $event->getAvailableLocale($request->getLocale()));

            /** @var PaginatedResult $sheets */
            $sheets = $this->get('query.agenda.sheet_list_view_query_handler')->handle($query);

            return new JsonResponse([
                'html' => $this->renderView('AdminBundle:Agenda:sheets-list.html.twig', [
                    'event'  => $event,
                    'sheets' => $sheets,
                ]),
            ]);
        }

        return $this->render('AdminBundle:Agenda:index.html.twig', [
            'event'  => $event,
            'sheets' => $sheets,
        ]);
    }
}
