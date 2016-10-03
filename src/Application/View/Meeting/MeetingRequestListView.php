<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Meeting;

use Proximum\Vimeet\Domain\Model\Sheet\Constant;

class MeetingRequestListView
{
    /**
     * @var MeetingRequestView[]
     */
    private $meetingRequestsView;

    /**
     * @return MeetingRequestView[]
     */
    public function getMeetingRequestsView()
    {
        return $this->meetingRequestsView;
    }

    /**
     * @param MeetingRequestView $meetingRequestView
     */
    public function addRequestView(MeetingRequestView $meetingRequestView)
    {
        $this->meetingRequestsView[] = $meetingRequestView;
    }

    /**
     * @param string $order
     */
    public function sortBy($order)
    {
        switch ($order) {
            case Constant::ORDER_BY_ALPHABETICAL:
                uasort($this->meetingRequestsView, function ($viewA, $viewB) {
                    if ($viewA->sheetName === $viewB->sheetName) {
                        return 0;
                    }

                    return $viewA->sheetName < $viewB->sheetName ? -1 : 1;
                });
                break;
        }
    }
}
