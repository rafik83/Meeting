<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Tip;

use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;

class CreateHandler
{
    /** @var TipRepositoryInterface */
    private $tipRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(TipRepositoryInterface $tipRepository, \DateTimeInterface $dateTime)
    {
        $this->tipRepository = $tipRepository;
        $this->dateTime      = $dateTime;
    }
    
    /**
     * @param Create $command
     */
    public function handle(Create $command)
    {
        $tip = new Tip(
            $command->title,
            $command->onMeetingManagement,
            $command->onCatalog,
            $command->onPrintPlanning,
            $this->dateTime
        );

        foreach ($command->translations as $translation) {
            $tip->translate($translation['locale'], $translation['title'], $translation['content']);
        }

        $this->tipRepository->add($tip);
    }
}
