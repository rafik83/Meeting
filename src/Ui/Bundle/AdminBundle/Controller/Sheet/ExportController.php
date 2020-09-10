<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Sheet;

use Proximum\Vimeet\Application\Query\Sheet;
use Proximum\Vimeet\Domain\ConditionRules\Storage\RuleStorageInterface;
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
     * @return CsvFileResponse
     */
    public function exportSheetAction(AdminDomain $adminDomain, Request $request, Event $event): CsvFileResponse
    {
        // Only super admin & organizers are allowed to export sheets:
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $displayNomenclatureIds = $request->query->getBoolean('displayNomenclatureIds');
        $locale = $event->getAvailableLocale($request->getLocale());
        $exportQuery = new Sheet\Export\ExportQuery(
            $event,
            $this->getFilters($event, $adminDomain->getAdmin(), $locale),
            $locale,
            $displayNomenclatureIds,
            $this->get(RuleStorageInterface::class)->getRules($event, $locale, 'sheet')
        );

        $result = $this->get('query.sheet.export_handler')->handle($exportQuery);

        return new CsvFileResponse(
            $result,
            sprintf('export_event_sheets_%s.csv', date('Y_m_d_His')),
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
