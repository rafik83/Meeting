<?php

/*
 * This file is part of the Proximum Vimeet website.
 *
 * Copyright © Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Agenda;

use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\View\Normalizer\EventParticipantSchedulesNormalizerView;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

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
     * @param Request $request
     * @param Event   $event
     *
     * @return Response
     */
    public function exportParticipantSchedulesAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $charset    = Charset::WINDOWS_1252;
        $serializer = $this->get('serializer');
        $exportContent = $serializer->serialize(new EventParticipantSchedulesNormalizerView($event, $this->getUser()), 'csv', [
            'locale'  => $event->getAvailableLocale($request->getLocale()),
            'charset' => $charset,
        ]);

        $response    = new Response($exportContent);
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            "export_participant_schedules_".date("Y_m_d_His").".csv"
        );
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', sprintf('text/csv; charset=%s', $charset));

        return $response;
    }
}
