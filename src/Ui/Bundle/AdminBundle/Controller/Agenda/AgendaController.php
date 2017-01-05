<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Agenda;

use Proximum\Vimeet\Application\Query\Sheet\PaginatedSheetListViewQuery;
use Proximum\Vimeet\Application\View\Sheet\SheetListView;
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
     * @return Response
     */
    public function indexAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        return $this->render('AdminBundle:Agenda:index.html.twig', [
            'event'  => $event,
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

        $query = new PaginatedSheetListViewQuery(
            $event,
            ['enable' => true],
            $request->query->getInt('page', 1),
            1000,
            $request->getLocale(),
            $this->getUser()
        );

        /** @var PaginatedResult $sheets */
        $paginatedResult = $this->get('tactician.commandbus.query')->handle($query);

        $sheetsList = [];

        /** @var SheetListView $sheet */
        foreach ($paginatedResult->results as $sheet) {
            $sheetsList[] = [
                'id'               => $sheet->id,
                'title'            => $sheet->title,
                'type'             => $sheet->type,
                'countParticipant' => $sheet->countParticipant,
                'url'              => $this->get('router')->generate(
                    'admin_sheet_details',
                    ['event' => $event->getId(), 'sheet' => $sheet->id]
                ),
            ];
        }

        return new JsonResponse($sheetsList);
    }
}
