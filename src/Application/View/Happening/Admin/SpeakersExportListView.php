<?php

namespace Proximum\Vimeet\Application\View\Happening\Admin;

class SpeakersExportListView
{
    /** @var SpeakerExportView[] */
    private $speakersListView;

    public function __construct(array $speakersListView)
    {
        $this->speakersListView = $speakersListView;
    }

    /**
     * @return SpeakerExportView[]
     */
    public function getSpeakersListView(): array
    {
        return $this->speakersListView;
    }
}
