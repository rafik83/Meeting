<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\MeetingRequest;

use Proximum\Vimeet\Application\Exception\Sheet\SheetNotFoundException;
use Proximum\Vimeet\Application\Query\Meeting\MeetingSheetViewQuery;
use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\HttpFoundation\Response\CsvFileResponse;

class ContactController extends Controller
{
    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     *
     * @return Response|CSVFileResponse
     */
    public function exportContactAction(Request $request, EventDomain $eventDomain)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_OPEN_ACCESS', $eventDomain->getEvent());

        try {
            $meetingSheetListView = $this->get('tactician.commandbus.query')->handle(
                new MeetingSheetViewQuery(
                    $this->getUser(),
                    $eventDomain->getEvent(),
                    $request->getLocale()
                )
            );
        } catch (SheetNotFoundException $exception) {
            throw $this->createNotFoundException('Sheet not found');
        }

        $charset       = Charset::WINDOWS_1252;
        $exportContent = $this->get('serializer')->serialize($meetingSheetListView, 'csv', [
            'charset' => $charset,
        ]);

        return new CsvFileResponse($exportContent, "export_contacts_" . date("Y_m_d_His") . ".csv");
    }
}
