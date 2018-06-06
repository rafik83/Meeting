<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Participant;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Query\Participant\Export\ExportQuery;
use Proximum\Vimeet\Application\Query\Participant\Export\ExportQueryHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Filter\SheetFilterSubmittedDataGetter;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\HttpFoundation\Response\CsvFileResponse;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ExportAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var SheetFilterSubmittedDataGetter */
    private $sheetFilterSubmittedDataGetter;

    /** @var ExportQueryHandler */
    private $exportQueryHandler;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        SheetFilterSubmittedDataGetter $sheetFilterSubmittedDataGetter,
        ExportQueryHandler $exportQueryHandler
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->sheetFilterSubmittedDataGetter = $sheetFilterSubmittedDataGetter;
        $this->exportQueryHandler = $exportQueryHandler;
    }

    /**
     * CSV export of participant's filtered sheets. Requires super admin or organizer role.
     *
     * @param AdminDomain $adminDomain
     * @param Request     $request
     * @param Event       $event
     *
     * @return CsvFileResponse
     */
    public function __invoke(AdminDomain $adminDomain, Request $request, Event $event): CsvFileResponse
    {
        if (false === $this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            || false ===  $this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
        ) {
            throw new AccessDeniedException('Access denied');
        }

        $locale = $event->getAvailableLocale($request->getLocale());
        $exportQuery = new ExportQuery(
            $event,
            $this->sheetFilterSubmittedDataGetter->handle($event, $adminDomain->getAdmin(), $locale),
            $locale
        );

        return new CsvFileResponse(
            $this->exportQueryHandler->handle($exportQuery),
            sprintf('export_event_participants_%s.csv', date('Y_m_d_His')),
            Response::HTTP_OK,
            [],
            $exportQuery->charset
        );
    }
}
