<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Happening;

use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;

class CreateHandler
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
     * @param Create $create
     */
    public function handle(Create $create)
    {
        $happening = new Happening($create->event, $create->begin, $create->end, $create->category);

        foreach ($create->titleTranslations as $locale => $translation) {
            $happening->setTitleTranslation(new Happening\TitleTranslation($happening, $locale, $translation['title']));
        }

        foreach ($create->descriptionTranslations as $locale => $translation) {
            $happening->setDescriptionTranslation(new Happening\DescriptionTranslation($happening, $locale, $translation['description']));
        }

        $this->happeningRepository->add($happening);
    }
}
