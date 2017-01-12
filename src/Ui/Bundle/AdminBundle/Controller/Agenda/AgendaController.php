<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Agenda;

use Proximum\Vimeet\Application\Query\Agenda\Admin\AgendaSheetViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\SheetListViewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
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

        return $this->render('AdminBundle:Agenda:index.html.twig', [
            'event' => $event,
        ]);
    }

    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return JsonResponse
     */
    public function sheetsListAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $sheets = $this->get('query.agenda.sheet_list_view_query_handler')->handle(
            new SheetListViewQuery($event, $event->getAvailableLocale($request->getLocale()))
        );

        return new JsonResponse($sheets);
    }

    /**
     * @param Request $request
     * @param Event   $event
     * @param Sheet   $sheet
     *
     * @return JsonResponse
     */
    public function sheetAction(Request $request, Event $event, Sheet $sheet)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        if ($sheet->getEvent() !== $event) {
            return new JsonResponse('Sheet are not on this event', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $agendaSheetView = $this->get('tactician.commandbus.query')->handle(
            new AgendaSheetViewQuery($sheet, $event->getAvailableLocale($request->getLocale()))
        );

        return new JsonResponse($agendaSheetView);
    }
}
