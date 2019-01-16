<?php

namespace Proximum\Vimeet\Application\View\Flux;

class ParticipantListView
{
    /** @var ParticipantView[] */
    public $participantViews;

    public function __construct(array $participantViews)
    {
        $this->participantViews = $participantViews;
    }
}
