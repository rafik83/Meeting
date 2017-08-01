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
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\HttpFoundation\Response\CsvFileResponse;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\SheetFilterType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class ExportController extends Controller
{
    /**
     * CSV export of event's filtered sheets. Requires super admin or organizer role.
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

        $locale      = $event->getAvailableLocale($request->getLocale());
        $exportQuery = new Sheet\Export\ExportQuery($event, $this->getFilters($event, $user, $locale), $locale);

        return new CsvFileResponse(
            $this->get('query.sheet.export_handler')->handle($exportQuery),
            sprintf('export_event_sheets_%s.csv', date("Y_m_d_His")),
            Response::HTTP_OK,
            [],
            $exportQuery->charset
        );
    }

    /**
     * CSV export of participant's filtered sheets. Requires super admin or organizer role.
     *
     * @param UserInterface $user
     * @param Request       $request
     * @param Event         $event
     *
     * @return Response
     */
    public function exportParticipantAction(UserInterface $user, Request $request, Event $event)
    {
        // Only super admin & organizers are allowed to export participants:
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $locale      = $event->getAvailableLocale($request->getLocale());
        $exportQuery = new Participant\Export\ExportQuery($event, $this->getFilters($event, $user, $locale), $locale);

        return new CsvFileResponse(
            $this->get('query.participant.export_handler')->handle($exportQuery),
            sprintf('export_event_participants_%s.csv', date("Y_m_d_His")),
            Response::HTTP_OK,
            [],
            $exportQuery->charset
        );
    }

    /**
     * @param Event         $event
     * @param UserInterface $user
     * @param string        $locale
     *
     * @return mixed
     */
    private function getFilters(Event $event, UserInterface $user, $locale)
    {
        $filters = $this->get('filter.sheet_filter')->get($event);

        if (null === $filters) {
            $filters = SheetFilterType::getDefaultFilters();
        }

        $sheetFilterForm = $this->createFilterForm(
            SheetFilterType::class,
            $filters,
            [
                'event'  => $event,
                'locale' => $locale,
                'user'   => $user,
            ]
        );

        $sheetFilterForm->submit($filters);

        return $sheetFilterForm->getData();
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
        return $this->get('form.factory')->createNamed(
            '',
            $type,
            $data,
            array_merge(
                $options,
                [
                    'method'             => 'GET',
                    'csrf_protection'    => false,
                    'required'           => false,
                    'allow_extra_fields' => true,
                ]
            )
        );
    }
}
