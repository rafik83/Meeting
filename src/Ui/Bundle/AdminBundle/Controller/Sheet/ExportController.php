<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Sheet;

use Proximum\Vimeet\Application\Query\Participant;
use Proximum\Vimeet\Application\Query\Sheet;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Filter\SheetFilterSubmittedDataGetter;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\HttpFoundation\Response\CsvFileResponse;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ExportController extends Controller
{
    /**
     * CSV export of event's filtered sheets. Requires super admin or organizer role.
     *
     * @param AdminDomain $adminDomain
     * @param Request     $request
     * @param Event       $event
     *
     * @return Response
     */
    public function exportSheetAction(AdminDomain $adminDomain, Request $request, Event $event)
    {
        // Only super admin & organizers are allowed to export sheets:
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $locale = $event->getAvailableLocale($request->getLocale());
        $exportQuery = new Sheet\Export\ExportQuery(
            $event,
            $this->getFilters($event, $adminDomain->getAdmin(), $locale),
            $locale
        );

        return new CsvFileResponse(
            $this->get('query.sheet.export_handler')->handle($exportQuery),
            sprintf('export_event_sheets_%s.csv', date('Y_m_d_His')),
            Response::HTTP_OK,
            [],
            $exportQuery->charset
        );
    }

    /**
     * CSV export of participant's filtered sheets. Requires super admin or organizer role.
     *
     * @param AdminDomain $adminDomain
     * @param Request     $request
     * @param Event       $event
     *
     * @return Response
     */
    public function exportParticipantAction(AdminDomain $adminDomain, Request $request, Event $event)
    {
        // Only super admin & organizers are allowed to export participants:
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $locale = $event->getAvailableLocale($request->getLocale());
        $exportQuery = new Participant\Export\ExportQuery(
            $event,
            $this->getFilters($event, $adminDomain->getAdmin(), $locale),
            $locale
        );

        return new CsvFileResponse(
            $this->get('query.participant.export_handler')->handle($exportQuery),
            sprintf('export_event_participants_%s.csv', date('Y_m_d_His')),
            Response::HTTP_OK,
            [],
            $exportQuery->charset
        );
    }

    /**
     * @param Event  $event
     * @param Admin  $admin
     * @param string $locale
     *
     * @return mixed
     */
    private function getFilters(Event $event, Admin $admin, $locale)
    {
        return $this->get(SheetFilterSubmittedDataGetter::class)->handle($event, $admin, $locale);
    }
}
