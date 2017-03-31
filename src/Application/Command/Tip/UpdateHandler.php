<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Tip;

use Proximum\Vimeet\Application\Exception\Tip\TipException;
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
     *
     * @throws TipException
     */
    public function handle(Update $command)
    {
        foreach($command->translations as $translation) {
            if (!$command->translations->containsKey($translation->locale)) {
                $command->tip = $command->tip->addTranslation($translation);
                $translation->tip = $command->tip;
            }
        }

        $this->tipRepository->set(
            $command->tip->update(
                $command->title,
                $command->onMeetingManagement,
                $command->onCatalog,
                $command->onPrintPlanning,
                $command->tip->translations
            )
        );
    }
}
