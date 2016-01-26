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
        $happening->update($update->begin, $update->end, $update->category);


        foreach ($update->titleTranslations as $locale => $translation) {
            $happening->updateTitleTranslation($locale, $translation['title']);
        }

        foreach ($update->descriptionTranslations as $locale => $translation) {
            $happening->updateDescriptionTranslation($locale, $translation['description']);
        }

        $this->happeningRepository->set($happening);
    }
}
