<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Sheet;

use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\View\Normalizer\EventParticipantsNormalizerView;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class ExportController extends Controller
{
    /**
     * CSV export of event's sheets. Requires super admin or organizer role.
     *
     * @param Request $request
     * @param Event   $event
     *
     * @return Response
     */
    public function exportSheetAction(Request $request, Event $event)
    {
        // Only super admin & organizers are allowed to export sheets:
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $locale = $event->getAvailableLocale($request->getLocale());

        $charset       = Charset::WINDOWS_1252;
        $serializer    = $this->get('serializer');
        $exportContent = $serializer->serialize($event, 'csv', [
            'locale'  => $locale,
            'charset' => $charset,
        ]);

        $response    = new Response($exportContent);
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            "export_event_sheets_" . date("Y_m_d_His") . ".csv"
        );
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', sprintf('text/csv; charset=%s', $charset));

        return $response;
    }

    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return Response
     */
    public function exportParticipantAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $charset        = Charset::WINDOWS_1252;
        $normaliserView = new EventParticipantsNormalizerView($event);

        $serializer    = $this->get('serializer');
        $exportContent = $serializer->serialize($normaliserView, 'csv', [
            'locale'  => $event->getAvailableLocale($request->getLocale()),
            'charset' => $charset,
        ]);

        $response    = new Response($exportContent);
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            "export_event_participant_" . date("Y_m_d_His") . ".csv"
        );
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', sprintf('text/csv; charset=%s', $charset));

        return $response;
    }

    /**
     * @param string $type
     * @param array  $data
     * @param array  $options
     *
     * @return FormInterface
     */
    private function createFilterForm($type, $data, array $options = [])
    {
        return $this->get('form.factory')->createNamed('', $type, $data, array_merge($options, [
            'method'             => 'GET',
            'csrf_protection'    => false,
            'required'           => false,
            'allow_extra_fields' => true,
        ]));
    }
}
