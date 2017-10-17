<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\MeetingRequest;

use Proximum\Vimeet\Application\Query\MeetingRequest\Export\MeetingRequestListViewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\HttpFoundation\Response\CsvFileResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ExportController extends Controller
{
    /**
     * @param Event $event
     *
     * @return CsvFileResponse
     */
    public function exportAction(Event $event): CsvFileResponse
    {
        $view = $this->get('tactician.commandbus.query')->handle(new MeetingRequestListViewQuery($event));

        return new CsvFileResponse(
            $this->get('serializer')->serialize($view, 'csv', ['csv_delimiter' => ';']),
            'export-meeting-request.csv'
        );
    }
}
