<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Happening;

use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;

class UpdateHandler
{
    /**
     * @var HappeningRepositoryInterface
     */
    private $happeningRepository;

    /**
     * @param HappeningRepositoryInterface $happeningRepository
     */
    public function __construct(HappeningRepositoryInterface $happeningRepository)
    {
        $this->happeningRepository = $happeningRepository;
    }

    /**
     * @param Update $update
     */
    public function handle(Update $update)
    {
        $happening = $update->happening;
        $happening->update(
            $update->begin,
            $update->end,
            $update->category,
            $update->allowQuestion,
            $update->limitParticipant
        );

        foreach ($update->translations as $locale => $translation) {
            $happening->updateTranslation($locale, $translation['title'], $translation['description']);
        }

        array_walk($update->talkings, function (array &$talking, $key) {
            $talking['position'] += $key;
        });

        // Sort speakers by position
        usort($update->talkings, function (array $one, array $another) { return $one['position'] - $another['position']; });

        // Set speakers
        $happening->setSpeakers(array_map(function (array $talking) { return $talking['speaker']; }, $update->talkings));

        $this->happeningRepository->set($happening);
    }
}
