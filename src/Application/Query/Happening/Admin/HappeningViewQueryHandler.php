<?php

namespace Proximum\Vimeet\Application\Query\Happening\Admin;

use DateTimeInterface;
use Proximum\Vimeet\Application\View\Happening\Admin\HappeningView;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;

class HappeningViewQueryHandler
{
    /** @var HappeningParticipationRepositoryInterface */
    private $participationRepository;

    /** @var SpeakerViewQueryHandler */
    private $speakerViewQueryHandler;

    /** @var DateTimeInterface */
    private $dateTime;

    public function __construct(
        HappeningParticipationRepositoryInterface $participationRepository,
        SpeakerViewQueryHandler $speakerViewQueryHandler,
        DateTimeInterface $dateTime
    ) {
        $this->participationRepository = $participationRepository;
        $this->speakerViewQueryHandler = $speakerViewQueryHandler;
        $this->dateTime = $dateTime;
    }

    public function handle(HappeningViewQuery $query): HappeningView
    {
        $happening = $query->happening;
        $speakers = $happening->getSpeakers();

        $speakerView = [];
        foreach ($speakers as $speaker) {
            $speakerView[] = $this->speakerViewQueryHandler->handle(new SpeakerViewQuery($speaker));
        }

        $participation = $this->participationRepository->countParticipationByHappening($happening);

        return new HappeningView(
            $happening->getId(),
            $happening->getTitle($query->locale),
            $happening->getCategory()->getTitle($query->locale),
            $happening->getBegin(),
            $happening->getEnd(),
            $happening->isQuestionAllowed(),
            $happening->getLimitParticipant(),
            $participation,
            $speakerView,
            $happening->isPrivate(),
            $happening->hasProducts(),
            $happening->isWebinar(),
            $happening->isInteractiveWebinar(),
            $happening->isVideoWebinar(),
            $happening->isWebinarRecorded(),
            $happening->isWebinarRecorded() && $happening->getEnd() < $this->dateTime,
            $happening->getWebinarRecordZipFileUrl()
        );
    }
}
