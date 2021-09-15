<?php

namespace Proximum\Vimeet\Application\View\Planner\Result;

class MeetingResult
{
    /** @var SpotResult|null */
    public $spot;

    /** @var SlotResult|null */
    public $slot;

    /** @var int */
    public $requestId;

    /** @var SheetResult */
    public $sheetFrom;

    /** @var SheetResult */
    public $sheetTo;

    /** @var UserResult[] */
    public $userResults;

    /** @var bool */
    public $isBlockedSlot = false;

    /** @var bool */
    public $isBlockedSpot = false;

    /**
     * @param UserResult $userResult
     */
    public function addUser(UserResult $userResult)
    {
        $this->userResults[] = $userResult;
    }
}
