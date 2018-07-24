<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Catalog;

use Proximum\Vimeet\Domain\Model\Event;

class CatalogTagFilter
{
    public const TYPE_INTERNAL = 'internal';
    public const TYPE_EXTERNAL = 'external';

    /** @var int */
    private $id;

    /** @var Event */
    private $event;

    /** @var string */
    private $tag;

    /** @var string */
    private $type;

    /** @var CatalogTagFilterTranslation[] */
    private $translations;

    public function __construct(
        Event $event,
        string $tag,
        string $type
    ) {
        $this->event = $event;
        $this->tag = $tag;
        $this->type = $type;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTranslations(): iterable
    {
        return $this->translations;
    }

    public function addTranslation(CatalogTagFilterTranslation $catalogTagFilterTranslation): void
    {
        $this->translations[] = $catalogTagFilterTranslation;
        $catalogTagFilterTranslation->setCatalogTagFilter($this);
    }
}
