<?php

namespace Proximum\Vimeet\Application\View\Happening\Admin;

use Proximum\Vimeet\Application\View\Agenda\SpeakerView;

class SpeakersExportListView
{
    /** @var SpeakerView[] */
    private $speakersListView;

    public function __construct(array $speakersListView)
    {
        $this->speakersListView = $speakersListView;
    }

    /**
     * @return SpeakerView[]
     */
    public function getSpeakersListView(): array
    {
        return $this->speakersListView;
    }
}
