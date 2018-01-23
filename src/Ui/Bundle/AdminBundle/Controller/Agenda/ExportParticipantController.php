<?php

/*
 * This file is part of the Proximum Vimeet website.
 *
 * Copyright © Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Agenda;

use Proximum\Vimeet\Application\Command\OMZ\Export;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\HttpFoundation\Response\CsvFileResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class ExportParticipantController extends Controller
{
    /**
     * @param Event $event
     *
     * @return Response
     */
    public function indexAction(Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        return $this->render('AdminBundle:Agenda:export.html.twig', [
            'event' => $event,
        ]);
    }

    /**
     * @param Event $event
     *
     * @return Response
     */
    public function exportParticipantSchedulesAction(Event $event)
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $export = new Export($event);
        $exportContent = $this->get('command.omz.export_handler')->handle($export);

        return new CsvFileResponse($exportContent, "export_participant_schedules_".date("Y_m_d_His").".csv");
    }
}
