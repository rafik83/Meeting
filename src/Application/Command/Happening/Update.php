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
    public $translations = [];

    /**
     * @var array
     */
    public $talkings = [];


    /**
     * @var bool
     */
    public $allowQuestion;

    /**
     * @var int|null
     */
    public $limitParticipant;

    /**
     * @var bool
     */
    public $questionAllowed;

    /**
     * @var int|null
     */
    public $limitParticipant;

    /**
     * Update constructor.
     *
     * @param Happening $happening
     */
    public function __construct(Happening $happening)
    {
        $this->happening        = $happening;
        $this->category         = $happening->getCategory();
        $this->begin            = $happening->getBegin();
        $this->end              = $happening->getEnd();
        $this->questionAllowed  = $happening->isQuestionAllowed();
        $this->limitParticipant = $happening->getLimitParticipant();

        foreach ($happening->getEvent()->getLocales() as $locale) {
            if ($happening->getTranslations()->containsKey($locale)) {
                $translation = $happening->getTranslations()->get($locale);

                $this->translations[$locale] = [
                    'title'       => $translation->getTitle(),
                    'description' => $translation->getDescription(),
                ];
            } else {
                $this->translations[$locale] = [
                    'title'       => '',
                    'description' => '',
                ];
            }
        }

        foreach ($happening->getSpeakers() as $position => $speaker) {
            $this->talkings[] = [
                'speaker'  => $speaker,
                'position' => $position,
            ];
        }
    }
}
