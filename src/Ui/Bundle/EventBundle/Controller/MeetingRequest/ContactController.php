<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\MeetingRequest;

use Proximum\Vimeet\Application\Query\Meeting\MeetingSheetViewQuery;
use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\HttpFoundation\Response\CsvFileResponse;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter\EventOpenAccessVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ContactController extends Controller
{
    public function exportContactAction(
        Request $request,
        EventDomain $eventDomain,
        UserDomain $userDomain,
        Sheet $sheet
    ): CsvFileResponse {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);
        $this->denyAccessUnlessGranted(EventOpenAccessVoter::PERMISSION_EVENT_OPEN_ACCESS, $eventDomain->getEvent());

        $meetingSheetListView = $this->get('tactician.commandbus.query')->handle(
            new MeetingSheetViewQuery(
                $eventDomain->getEvent(),
                $userDomain->getUser(),
                $sheet,
                $request->getLocale()
            )
        );

        $charset       = Charset::WINDOWS_1252;
        $exportContent = $this->get('serializer')->serialize($meetingSheetListView, 'csv', [
            'charset'       => $charset,
            'csv_delimiter' => ';',
        ]);

        return new CsvFileResponse($exportContent, 'export_contacts_' . date('Y_m_d_His') . '.csv');
    }
}
