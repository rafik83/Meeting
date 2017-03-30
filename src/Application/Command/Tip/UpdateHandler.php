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
        if (!$command->tip) {
            throw new TipException();
        }

        foreach($command->translations as $translation) {
            $command->tip->addTranslation($translation);
            $translation->tip = $command->tip;
        }
    
        foreach($command->tip->getTranslations() as $translation) {
            if(!in_array($translation->id, $command->translations->getKeys())) {
                $command->tip->removeTranslation($translation);
            }
        }
        
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
