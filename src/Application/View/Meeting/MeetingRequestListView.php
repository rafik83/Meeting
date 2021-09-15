<?php

namespace Proximum\Vimeet\Application\View\Meeting;

use Proximum\Vimeet\Domain\Model\Sheet\Constant;

class MeetingRequestListView
{
    /** @var MeetingRequestView[] */
    private $meetingRequestsView = [];

    /**
     * @return MeetingRequestView[]
     */
    public function getMeetingRequestsView(): array
    {
        return $this->meetingRequestsView;
    }

    /**
     * @param MeetingRequestView $meetingRequestView
     */
    public function addRequestView(MeetingRequestView $meetingRequestView): void
    {
        $this->meetingRequestsView[] = $meetingRequestView;
    }

    /**
     * @param string $order
     */
    public function sortBy($order): void
    {
        if (empty($this->meetingRequestsView)) {
            return;
        }

        switch ($order) {
            case Constant::ORDER_BY_ALPHABETICAL:
                uasort($this->meetingRequestsView, function (
                    MeetingRequestView $viewA,
                    MeetingRequestView $viewB
                ) {
                    return strcmp($viewA->sheetName, $viewB->sheetName);
                });
                break;
        }
    }
}
