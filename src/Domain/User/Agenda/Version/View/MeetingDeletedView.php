<?php

namespace Proximum\Vimeet\Domain\User\Agenda\Version\View;

class MeetingDeletedView
{
    /** @var int */
    public $sheetId;

    /** @var int */
    public $requestId;

    /**
     * @param int $sheetId
     * @param int $requestId
     */
    public function __construct(int $sheetId, int $requestId)
    {
        $this->sheetId = $sheetId;
        $this->requestId = $requestId;
    }
}
