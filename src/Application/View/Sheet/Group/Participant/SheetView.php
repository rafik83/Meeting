<?php

namespace Proximum\Vimeet\Application\View\Sheet\Group\Participant;

class SheetView
{
    /** @var int */
    public $id;

    /** @var string */
    public $title;

    /** @var ParticipantView[] */
    public $participantViews;

    /**
     * @param int               $id
     * @param string            $title
     * @param ParticipantView[] $participantViews
     */
    public function __construct($id, $title, array $participantViews)
    {
        $this->id               = $id;
        $this->title            = $title;
        $this->participantViews = $participantViews;
    }
}
