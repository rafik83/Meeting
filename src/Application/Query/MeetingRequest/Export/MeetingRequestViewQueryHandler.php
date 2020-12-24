<?php

namespace Proximum\Vimeet\Application\Query\MeetingRequest\Export;

use Proximum\Vimeet\Application\View\MeetingRequest\Export\MeetingRequestView;
use Proximum\Vimeet\Domain\Model\Meeting\Request;

class MeetingRequestViewQueryHandler
{
    /** @var SheetViewQueryHandler */
    private $sheetViewQueryHandler;

    /**
     * @param SheetViewQueryHandler $sheetViewQueryHandler
     */
    public function __construct(SheetViewQueryHandler $sheetViewQueryHandler)
    {
        $this->sheetViewQueryHandler = $sheetViewQueryHandler;
    }

    /**
     * @param MeetingRequestViewQuery $query
     *
     * @return MeetingRequestView
     */
    public function handle(MeetingRequestViewQuery $query): MeetingRequestView
    {
        $fromSheet = $this->sheetViewQueryHandler->handle(
            new SheetViewQuery(
                $query->request->getFromSheet(),
                $query->request->getFromParticipantsArray(),
                $query->locale
            )
        );

        $toSheet = $this->sheetViewQueryHandler->handle(
            new SheetViewQuery(
                $query->request->getToSheet(),
                $query->request->getToParticipantsArray(),
                $query->locale
            )
        );

        return new MeetingRequestView(
            $query->request->getId(),
            $query->request->hasMeeting() ? $query->request->getMeeting()->getId() : null,
            $fromSheet,
            $toSheet,
            $query->request->hasMeeting() ? Request::STATE_PLANNED : $query->request->getState(),
            $query->request->getCreatedAt(),
            $query->request->getStateUpdatedAt(),
            $query->request->getMeeting() ? $query->request->getMeeting()->getCreatedType() : null,
            $query->request->getMeeting() ? $query->request->getMeeting()->getSlot()->getBegin() : null
        );
    }
}
