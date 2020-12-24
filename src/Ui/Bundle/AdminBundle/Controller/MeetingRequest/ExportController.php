<?php

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
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');

        $view = $this->get('tactician.commandbus.query')->handle(new MeetingRequestListViewQuery($event));

        return new CsvFileResponse(
            $this->get('serializer')->serialize($view, 'csv', ['csv_delimiter' => ';']),
            sprintf('export_meeting_request_%s_%s.csv', $event->getId(), date('Y_m_d_His'))
        );
    }
}
