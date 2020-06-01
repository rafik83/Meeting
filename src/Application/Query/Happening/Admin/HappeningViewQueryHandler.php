<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Happening\Admin;

use Proximum\Vimeet\Application\View\Happening\Admin\HappeningView;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;

class HappeningViewQueryHandler
{
    /**
     * @var HappeningParticipationRepositoryInterface
     */
    private $participationRepository;

    /**
     * @var SpeakerViewQueryHandler
     */
    private $speakerViewQueryHandler;

    /**
     * @param HappeningParticipationRepositoryInterface $participationRepository
     * @param SpeakerViewQueryHandler                   $speakerViewQueryHandler
     */
    public function __construct(
        HappeningParticipationRepositoryInterface $participationRepository,
        SpeakerViewQueryHandler $speakerViewQueryHandler
    ) {
        $this->participationRepository = $participationRepository;
        $this->speakerViewQueryHandler = $speakerViewQueryHandler;
    }

    /**
     * @param HappeningViewQuery $query
     *
     * @return HappeningView
     */
    public function handle(HappeningViewQuery $query)
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
            $query->happening->isInteractiveWebinar()
        );
    }
}
