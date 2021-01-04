<?php

namespace Proximum\Vimeet\Application\Query\Meeting\Admin\ListMeeting;

use Proximum\Vimeet\Application\View\Meeting\Admin\ListMeeting\MeetingListView;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;

class MeetingListViewQueryHandler
{
    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    /** @var MeetingViewQueryHandler */
    private $meetingViewQueryHandler;

    public function __construct(
        MeetingRepositoryInterface $meetingRepository,
        MeetingViewQueryHandler $meetingViewQueryHandler
    ) {
        $this->meetingRepository = $meetingRepository;
        $this->meetingViewQueryHandler = $meetingViewQueryHandler;
    }

    public function handle(MeetingListViewQuery $query): MeetingListView
    {
        if (null === $query->slot) {
            return new MeetingListView(
                $this->meetingRepository->countByEvent($query->event),
                null
            );
        }

        $meetingListView = new MeetingListView(
            $this->meetingRepository->countByEvent($query->event),
            $query->slot
        );

        $meetings = $this->meetingRepository->findByMeetingSlot($query->slot);

        foreach ($meetings as $meeting) {
            $meetingListView->addMeetingView(
                $this->meetingViewQueryHandler->handle(new MeetingViewQuery($meeting, $query->locale))
            );
        }

        return $meetingListView;
    }
}
