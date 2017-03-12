<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Planning\Displayer;

use Proximum\Vimeet\Application\Adapter\MarkdownAdapterInterface;
use Proximum\Vimeet\Application\Components\Planning\Formatter\ParticipantPlanningFormatter;
use Proximum\Vimeet\Domain\Model\Participant;

class ParticipantPlanningDisplayer
{
    /**
     * @var ParticipantPlanningFormatter
     */
    private $participantPlanningFormatter;

    /**
     * @var MarkdownAdapterInterface
     */
    private $markdown;

    /**
     * @param ParticipantPlanningFormatter $participantPlanningFormatter
     * @param MarkdownAdapterInterface     $markdown
     */
    public function __construct(
        ParticipantPlanningFormatter $participantPlanningFormatter,
        MarkdownAdapterInterface $markdown
    ) {
        $this->participantPlanningFormatter = $participantPlanningFormatter;
        $this->markdown                     = $markdown;
    }

    /**
     * @param Participant $participant
     * @param string      $locale
     *
     * @return string
     */
    public function display(Participant $participant, $locale)
    {
        $planningMarkdown = $this
            ->participantPlanningFormatter
            ->formatPlanningFromParticipantWithUnallocated($participant, $locale);

        return $this->markdown->toHtml($planningMarkdown);
    }

    /**
     * To optimize the use of this service for multiple participant
     * This method can be called to preload the participants data before
     * And avoid multiple unit call for each participant
     *
     * @param Participant[] $participants
     */
    public function preloadForParticipants(array $participants)
    {
        $this->participantPlanningFormatter->preloadPlanningHandlerForParticipants($participants);
    }
}
