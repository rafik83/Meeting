<?php

namespace Proximum\Vimeet\Application\Query\Happening\Admin;

use Proximum\Vimeet\Application\View\Happening\Admin\HappeningView;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;

class HappeningViewQueryHandler
{
    /** @var HappeningParticipationRepositoryInterface */
    private $participationRepository;

    /** @var SpeakerViewQueryHandler */
    private $speakerViewQueryHandler;

    /** @var \DateTimeInterface */
    private $datetime;

    public function __construct(
        HappeningParticipationRepositoryInterface $participationRepository,
        SpeakerViewQueryHandler $speakerViewQueryHandler,
        \DateTimeInterface $datetime
    ) {
        $this->participationRepository = $participationRepository;
        $this->speakerViewQueryHandler = $speakerViewQueryHandler;
        $this->datetime = $datetime;
    }

    public function handle(HappeningViewQuery $query): HappeningView
    {
        $speakers = $query->happening->getSpeakers();

        $speakerView = [];
        foreach ($speakers as $speaker) {
            $speakerView[] = $this->speakerViewQueryHandler->handle(new SpeakerViewQuery($speaker));
        }

        $participation = $this->participationRepository->countParticipationByHappening($query->happening);

        return new HappeningView(
            $query->happening->getId(),
            $query->happening->getTitle($query->locale),
            $query->happening->getCategory()->getTitle($query->locale),
            $query->happening->getBegin(),
            $query->happening->getEnd(),
            $query->happening->isQuestionAllowed(),
            $query->happening->getLimitParticipant(),
            $participation,
            $speakerView,
            $query->happening->isPrivate(),
            $query->happening->hasProducts(),
            $query->happening->isWebinar(),
            $query->happening->isInteractiveWebinar(),
            $query->happening->isVideoWebinar(),
            $query->happening->isWebinarRecorded(),
            $query->happening->isWebinarRecorded() && $query->happening->getEnd() < $this->datetime
        );
    }
}
