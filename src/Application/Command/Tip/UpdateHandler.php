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
        foreach ($command->tip->getTranslations() as $translation) {
            if (!isset($command->translations[$translation->getLocale()])) {
                $command->tip->translations->remove($translation->getLocale());
                $this->tipRepository->removeTranslation($translation);
            }
        }

        foreach ($command->translations as $locale => $translation) {
            $command->tip->translate(
                $translation['locale'],
                $translation['title'],
                $translation['content']
            );
        }

        $this->tipRepository->set(
            $command->tip->update(
                $command->title,
                $command->onMeetingManagement,
                $command->onCatalog,
                $command->onPrintPlanning
            )
        );
    }
}
