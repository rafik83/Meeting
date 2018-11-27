<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Template\Form;

class FormTemplateView
{
    /** @var int */
    public $id;

    /** @var string */
    public $title;

    /** @var bool */
    public $isPublished;

    /** @var \DateTimeInterface */
    public $createdAt;

    /** @var string[] indexed by locale */
    public $translatedTitles;

    /** @var string */
    public $locale;

    public function __construct(
        int $id,
        string $title,
        string $locale,
        bool $isPublished,
        array $translatedTitles,
        \DateTimeInterface $createdAt
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->locale = $locale;
        $this->isPublished = $isPublished;
        $this->translatedTitles = $translatedTitles;
        $this->createdAt = $createdAt;
    }
}
