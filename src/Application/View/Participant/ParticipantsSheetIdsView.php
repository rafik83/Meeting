<?php

namespace Proximum\Vimeet\Application\View\Participant;

class ParticipantsSheetIdsView
{
    /** @var int[] Sheet id */
    public $sheetIds;

    /**
     * @param int[] $sheetIds
     */
    public function __construct(array $sheetIds)
    {
        $this->sheetIds = $sheetIds;
    }
}
