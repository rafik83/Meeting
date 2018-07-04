<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Catalog;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;

class CatalogTagFilterTranslation
{
    public const TYPE_INTERNAL = 'internal';
    public const TYPE_EXTERNAL = 'external';

    /** @var int */
    private $id;

    /** @var string */
    private $locale;

    /** @var string */
    private $label;

    /** @var string */
    private $placeholder;

    /** @var CatalogTagFilter */
    private $catalogTagFilter;

    public function __construct(string $locale, ?string $label, ?string $placeholder)
    {
        $this->locale = $locale;
        $this->label = $label;
        $this->placeholder = $placeholder;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function getPlaceholder(): ?string
    {
        return $this->placeholder;
    }

    public function getCatalogTagFilter(): CatalogTagFilter
    {
        return $this->catalogTagFilter;
    }
}
