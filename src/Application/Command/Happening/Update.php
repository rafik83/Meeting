<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Happening;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Happening\Category;

class Update
{
    /**
     * @var Happening
     */
    public $happening;

    /**
     * @var Category
     */
    public $category;

    /**
     * @var \DateTimeInterface
     */
    public $begin;

    /**
     * @var \DateTimeInterface
     */
    public $end;

    /**
     * @var array
     */
    public $titleTranslations = [];

    /**
     * @var array
     */
    public $descriptionTranslations = [];

    /**
     * Update constructor.
     *
     * @param Happening $happening
     */
    public function __construct(Happening $happening)
    {
        $this->happening = $happening;
        $this->category  = $happening->getCategory();
        $this->begin     = $happening->getBegin();
        $this->end       = $happening->getEnd();

        foreach ($happening->getTitleTranslations() as $titleTranslation) {
            $this->titleTranslations[$titleTranslation->getLocale()] = [
                'title' => $titleTranslation->getTitle(),
            ];
        }

        foreach ($happening->getDescriptionTranslations() as $descriptionTranslation) {
            $this->descriptionTranslations[$descriptionTranslation->getLocale()] = [
                'description' => $descriptionTranslation->getDescription(),
            ];
        }
    }
}
