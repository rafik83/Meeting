<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Unavailability\Mass;

use Proximum\Vimeet\Domain\Model\Unavailability\Mass;
use Proximum\Vimeet\Domain\Model\Unavailability\Category;

class Update
{

    /**
     * @var Mass
     */
    public $mass;

    /**
     * @var \DateTimeInterface
     */
    public $begin;

    /**
     * @var \DateTimeInterface
     */
    public $end;

    /**
     * @var bool
     */
    public $blocking;

    /**
     * @var string
     */
    public $name;

    /**
     * @var array
     */
    public $translations;

    /**
     * @var Category
     */
    public $category;

    /**
     * @param Mass $mass
     */
    public function __construct(Mass $mass)
    {
        $this->mass     = $mass;
        $this->begin    = $mass->getBegin();
        $this->end      = $mass->getEnd();
        $this->blocking = $mass->isBlocking();
        $this->name     = $mass->getName();
        $this->category = $mass->getCategory();

        foreach ($mass->getTranslations() as $locale => $translation){
            $this->translations[$locale] = [
                'title'       => $translation->getTitle(),
                'description' => $translation->getDescription(),
            ];
        }
    }
}
