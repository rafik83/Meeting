<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Happening;

use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;

class CreateHandler
{
    /** @var HappeningRepositoryInterface */
    private $happeningRepository;

    /**
     * @param HappeningRepositoryInterface $happeningRepository
     */
    public function __construct(HappeningRepositoryInterface $happeningRepository)
    {
        $this->happeningRepository = $happeningRepository;
    }

    /**
     * @param Create $create
     */
    public function handle(Create $create)
    {
        $happening = new Happening(
            $create->event,
            $create->begin,
            $create->end,
            $create->category,
            $create->types,
            $create->questionAllowed,
            $create->limitParticipant,
            $create->invitationCode,
            $create->visioConfEnabled
        );

        foreach ($create->translations as $locale => $translation) {
            $happening->setTranslation(new Happening\HappeningTranslation($happening, $locale, $translation['title'], $translation['description']));
        }

        // Sort speakers by position
        usort($create->talkings, function (array $one, array $another) { return $one['position'] - $another['position']; });

        // Set speakers
        $happening->setSpeakers(array_map(function (array $talking) { return $talking['speaker']; }, $create->talkings));

        $this->happeningRepository->add($happening);
    }
}
