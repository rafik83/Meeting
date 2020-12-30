<?php

namespace Proximum\Vimeet\Application\View\Happening\Admin;

class HappeningParticipantListView
{
    /**
     * @var HappeningParticipantView[]
     */
    private $happeningParticipantListView;

    /**
     * HappeningParticipantListView constructor.
     *
     * @param HappeningParticipantView[] $happeningParticipantListView
     */
    public function __construct(array $happeningParticipantListView)
    {
        $this->happeningParticipantListView = $happeningParticipantListView;
    }

    /**
     * @return HappeningParticipantView[]
     */
    public function getHappeningParticipantListView()
    {
        return $this->happeningParticipantListView;
    }
}
