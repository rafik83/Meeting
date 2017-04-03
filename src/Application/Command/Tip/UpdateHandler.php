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
        $removedTranslations = [];

        foreach ($command->translations as $locale => $translation) {
            if ($command->tip->translations->containsKey($locale)) {
                $removedTranslations[] = $command->tip->updateTranslation($locale, [
                    'title'   => $translation['title'],
                    'locale'  => $translation['locale'],
                    'content' => $translation['content'],
                ]);
            } else {
                $command->tip->setTranslation([
                    'title'   => $translation['title'],
                    'locale'  => $translation['locale'],
                    'content' => $translation['content'],
                ]);
            }
        }

        foreach($removedTranslations as $translation) {
            $this->tipRepository->removeTranslation($translation);
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
