<?php

namespace Proximum\Vimeet\Application\Query\MultipleSheets\Request;

use Proximum\Vimeet\Application\View\MultipleSheets\Request\RequestView;
use Proximum\Vimeet\Domain\Model\Participant;

class RequestViewQueryHandler
{
    /** @var ParticipantViewQueryHandler */
    private $participantViewQueryHandler;

    /**
     * @param ParticipantViewQueryHandler $participantViewQueryHandler
     */
    public function __construct(ParticipantViewQueryHandler $participantViewQueryHandler)
    {
        $this->participantViewQueryHandler = $participantViewQueryHandler;
    }

    /**
     * @param RequestViewQuery $query
     *
     * @return RequestView
     */
    public function handle(RequestViewQuery $query)
    {
        $sheetMet = $query->request->getSheetMet($query->sheet);

        return new RequestView(
            $query->request->getId(),
            $query->request,
            $sheetMet->getId(),
            $sheetMet->getTitle(),
            $sheetMet,
            $query->request->getState(),
            $query->request->isSender($sheetMet) ? RequestView::TYPE_REQUEST : RequestView::TYPE_PROPOSITION,
            array_map(function (Participant $participant) use ($query) {
                return $this->participantViewQueryHandler->handle(
                    new ParticipantViewQuery($participant, $query->locale)
                );
            }, $query->request->getParticipantsOfSheetInRequest($sheetMet)),
            $query->request->hasMeeting()
        );
    }
}
