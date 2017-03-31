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
use Proximum\Vimeet\Domain\Model\Tip\TipTranslation;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;

class CreateHandler
{
    /** @var TipRepositoryInterface */
    private $tipRepository;
    
    public function __construct(TipRepositoryInterface $tipRepository)
    {
        $this->tipRepository = $tipRepository;
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
            $command->onPrintPlanning
        );

        foreach ($command->translations as $translation) {
            $tip->setTranslation(
                new TipTranslation(
                    $tip,
                    $translation['title'],
                    $translation['locale'],
                    $translation['content']
                )
            );
        }

        $this->tipRepository->add($tip);
    }
}
