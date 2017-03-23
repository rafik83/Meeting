<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Sheet;

use Proximum\Vimeet\Application\Query\Sheet\Export\ExportQuery;
use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\View\Normalizer\EventParticipantsNormalizerView;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\SheetFilterType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Security\Core\User\UserInterface;

class ExportController extends Controller
{
    /**
     * CSV export of event's sheets. Requires super admin or organizer role.
     *
     * @param UserInterface $user
     * @param Request       $request
     * @param Event         $event
     *
     * @return Response
     */
    public function exportSheetAction(UserInterface $user, Request $request, Event $event)
    {
        // Only super admin & organizers are allowed to export sheets:
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $locale = $event->getAvailableLocale($request->getLocale());

        $filters = $this->get('filter.sheet_filter')->get();

        if (null === $filters) {
            $filters = SheetFilterType::getDefaultFilters();
        }

        $sheetFilterForm = $this->createFilterForm(SheetFilterType::class, $filters, [
            'event'  => $event,
            'locale' => $event->getAvailableLocale($request->getLocale()),
            'user'   => $user,
        ]);

        $sheetFilterForm->submit($filters);
        $filters = $sheetFilterForm->getData();

        $exportQuery = new ExportQuery($event, $filters, $locale);

        $response = new Response($this->get('query.sheet.export_handler')->handle($exportQuery));

        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            "export_event_sheets_" . date("Y_m_d_His") . ".csv"
        );
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', sprintf('text/csv; charset=%s', $exportQuery->charset));

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
