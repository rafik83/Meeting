<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Template\Form;

use Proximum\Vimeet\Domain\Model\Type;

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

    /** @var Type[] */
    public $types;

    public function __construct(
        int $id,
        string $title,
        bool $isPublished,
        \DateTimeInterface $createdAt,
        array $translatedTitles,
        string $locale,
        array $types
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->isPublished = $isPublished;
        $this->createdAt = $createdAt;
        $this->translatedTitles = $translatedTitles;
        $this->locale = $locale;
        $this->types = $types;
    }
}
