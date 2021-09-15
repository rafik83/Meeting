<?php

namespace Proximum\Vimeet\Application\View\Package;

class ParticipantsView
{
    /** @var ParticipantView[] */
    public $participantViews;

    /** @var ParticipantProductView[] */
    public $participantProductViews;

    /** @var string */
    public $serializedParticipantProductViews;

    /**
     * @param ParticipantView[]        $participantViews
     * @param ParticipantProductView[] $participantProductViews
     * @param string                   $serializedParticipantProductViews
     */
    public function __construct(
        array $participantViews,
        array $participantProductViews,
        string $serializedParticipantProductViews
    ) {
        $this->participantViews = $participantViews;
        $this->participantProductViews = $participantProductViews;
        $this->serializedParticipantProductViews = $serializedParticipantProductViews;
    }
}
