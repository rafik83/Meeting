<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\MeetingRequest;

use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Query\Meeting\MeetingSheetViewQuery;
use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\HttpFoundation\Response\CsvFileResponse;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter\EventOpenAccessVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class ContactController extends AbstractController
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

    public function exportContactAction(
        Request $request,
        EventDomain $eventDomain,
        UserDomain $userDomain,
        Sheet $sheet
    ): CsvFileResponse {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);
        $this->denyAccessUnlessGranted(EventOpenAccessVoter::PERMISSION_EVENT_OPEN_ACCESS, $eventDomain->getEvent());

        $meetingSheetListView = $this->queryBus->handle(
            new MeetingSheetViewQuery(
                $eventDomain->getEvent(),
                $userDomain->getUser(),
                $sheet,
                $request->getLocale()
            )
        );

        $charset       = Charset::WINDOWS_1252;
        $exportContent = $this->serializer->serialize($meetingSheetListView, 'csv', [
            'charset'       => $charset,
            'csv_delimiter' => ';',
        ]);

        return new CsvFileResponse($exportContent, 'export_contacts_' . date('Y_m_d_His') . '.csv');
    }
}
