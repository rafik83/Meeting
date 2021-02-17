<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\MeetingRequest;

use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Query\MeetingRequest\Export\MeetingRequestListViewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\HttpFoundation\Response\CsvFileResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ExportController extends AbstractController
{
    private SerializerAdapterInterface $serializer;
    private QueryBusInterface $queryBus;

    public function __construct(
        SerializerAdapterInterface $serializer,
        QueryBusInterface $queryBus
    ) {
        $this->serializer = $serializer;
        $this->queryBus = $queryBus;
    }

    public function exportAction(Event $event): CsvFileResponse
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');

        $view = $this->queryBus->handle(new MeetingRequestListViewQuery($event));

        return new CsvFileResponse(
            $this->serializer->serialize($view, 'csv', ['csv_delimiter' => ';']),
            sprintf('export_meeting_request_%s_%s.csv', $event->getId(), date('Y_m_d_His'))
        );
    }
}
