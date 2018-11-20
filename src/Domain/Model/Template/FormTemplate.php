<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Template;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\Model\Event;

class FormTemplate extends AbstractTemplate
{
    /** @var ArrayCollection of FormTemplateTranslation */
    private $translations;

    /** @var bool */
    private $published;

    public function __construct(
        Event $event,
        string $title,
        array $value,
        array $locales,
        string $fallback,
        \DateTimeInterface $createdAt
    ) {
        parent::__construct($title, $value, $locales, $fallback, $createdAt, $event);

        $this->translations = new ArrayCollection();
        $this->published = false;
    }

    /**
     * @return FormTemplateTranslation[]
     */
    public function getTranslations(): array
    {
        return $this->translations->toArray();
    }

    public function getFallback(): string
    {
        return $this->event ? $this->event->getFallback() : $this->fallback;
    }

    public function isPublished(): bool
    {
        return $this->published;
    }
}
