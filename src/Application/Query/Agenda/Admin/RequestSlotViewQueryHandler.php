<?php

namespace Proximum\Vimeet\Application\Query\Agenda\Admin;

use Proximum\Vimeet\Application\Exception\Meeting\MeetingRequestCanNotBeMeetingException;
use Proximum\Vimeet\Application\Exception\MeetingRequest\NoSlotAvailableException;
use Proximum\Vimeet\Application\Exception\MeetingRequest\NoSpotAvailableException;
use Proximum\Vimeet\Application\View\Agenda\Admin\RequestSlotView;
use Proximum\Vimeet\Domain\Meeting\MeetingParticipants;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;

class RequestSlotViewQueryHandler
{
    /** @var SpotRepositoryInterface */
    private $spotRepository;

    /** @var MeetingSlotRepositoryInterface */
    private $meetingSlotRepository;

    /** @var MeetingParticipants */
    private $meetingParticipants;

    /**
     * @param SpotRepositoryInterface        $spotRepository
     * @param MeetingSlotRepositoryInterface $meetingSlotRepository
     * @param MeetingParticipants            $meetingParticipants
     */
    public function __construct(
        SpotRepositoryInterface $spotRepository,
        MeetingSlotRepositoryInterface $meetingSlotRepository,
        MeetingParticipants $meetingParticipants
    ) {
        $this->spotRepository        = $spotRepository;
        $this->meetingSlotRepository = $meetingSlotRepository;
        $this->meetingParticipants = $meetingParticipants;
    }

    /**
     * @param RequestSlotViewQuery $query
     *
     * @throws MeetingRequestCanNotBeMeetingException
     * @throws NoSlotAvailableException
     * @throws NoSpotAvailableException
     *
     * @return RequestSlotView
     */
    public function handle(RequestSlotViewQuery $query)
    {
        if (false === $query->meetingRequest->isTransformableIntoMeeting()) {
            throw new MeetingRequestCanNotBeMeetingException();
        }

        $slots = $this->meetingSlotRepository->findAvailableSlotsByParticipants(
            $query->meetingRequest->getEvent(),
            $this->meetingParticipants->getAllMeetingParticipants($query->meetingRequest),
            false
        );

        if (0 === \count($slots)) {
            throw new NoSlotAvailableException();
        }

        $availableSlotsId = [];

        foreach ($slots as $slot) {
            if (true === $this->spotRepository->hasSpotsForSlotAndParticipantsQuantity(
                $slot,
                $this->meetingParticipants->countAllMeetingParticipants($query->meetingRequest),
                null,
                $query->meetingRequest->getFromSheet(),
                $query->meetingRequest->getToSheet(),
                $query->visio
            )) {
                $availableSlotsId[] = $slot->getId();
            }
        }

        if (0 === \count($availableSlotsId)) {
            throw new NoSpotAvailableException();
        }

        return new RequestSlotView($availableSlotsId);
    }
}
