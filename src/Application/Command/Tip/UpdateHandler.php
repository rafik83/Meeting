<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Tip;

use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;

class UpdateHandler
{
    /** @var TipRepositoryInterface */
    private $tipRepository;
    
    public function __construct(TipRepositoryInterface $tipRepository)
    {
        $this->tipRepository = $tipRepository;
    }
    
    /**
     * @param Update $command
     */
    public function handle(Update $command)
    {
        $this->tipRepository->set(
            $command->tip->update(
                $command->title,
                $command->onMeetingManagement,
                $command->onCatalog,
                $command->onPrintPlanning,
                $command->translations
            )
        );
    }
}
