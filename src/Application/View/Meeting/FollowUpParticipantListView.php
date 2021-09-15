<?php

namespace Proximum\Vimeet\Application\View\Meeting;

class FollowUpParticipantListView
{
    /** @var FollowUpParticipantView[] */
    public array $participantViews = [];

    /**
     * @param FollowUpParticipantView[] $participantViews
     */
    public function __construct(array $participantViews)
    {
        $this->participantViews = $participantViews;
    }
}
