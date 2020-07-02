<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Record;

use Proximum\Vimeet\Application\Adapter\VideoConferenceAdapterInterface;

class StopRecordHandler
{
    /** @var VideoConferenceAdapterInterface */
    private $videoConferenceAdapter;

    public function __construct(
        VideoConferenceAdapterInterface $videoConferenceAdapter
    ) {
        $this->videoConferenceAdapter = $videoConferenceAdapter;
    }

    public function handle(StopRecord $stopRecord): void
    {
        $happening = $stopRecord->happening;
    }
}
