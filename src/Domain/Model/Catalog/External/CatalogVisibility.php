<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Catalog\External;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;

class CatalogVisibility
{
    /** @var int */
    private $id;

    /** @var Event */
    private $event;

    /** @var ArrayCollection of Type */
    private $types;

    /** @var ArrayCollection of Category */
    private $categories;

    /**
     * CatalogVisibility constructor.
     *
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
        $this->types = new ArrayCollection();
        $this->categories = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Event
     */
    public function getEvent(): Event
    {
        return $this->event;
    }

    /**
     * @return array
     */
    public function getTypes(): array
    {
        return $this->types->toArray();
    }

    /**
     * @return array
     */
    public function getCategories(): array
    {
        return $this->categories->toArray();
    }

    /**
     * @param Type[] $types
     */
    public function updateTypes(array $types)
    {
        foreach ($types as $type) {
            if (!in_array($type, $this->getTypes())) {
                $this->setType($type);
            }
        }

        foreach ($this->getTypes() as $type) {
            if (!in_array($type, $types)) {
                $this->removeType($type);
            }
        }
    }

    /**
     * @param Type $type
     */
    public function setType(Type $type)
    {
        $this->types->set($type->getId(), $type);
    }

    /**
     * @param Type $type
     */
    public function removeType(Type $type)
    {
        $this->types->removeElement($type);
    }

    /**
     * @param Category[] $categories
     */
    public function updateCategories(array $categories)
    {
        foreach ($categories as $category) {
            if (!in_array($category, $this->getCategories())) {
                $this->setCategory($category);
            }
        }

        foreach ($this->getCategories() as $category) {
            if (!in_array($category, $categories)) {
                $this->removeCategory($category);
            }
        }
    }

    /**
     * @param Category $category
     */
    public function setCategory(Category $category)
    {
        $this->categories->set($category->getId(), $category);
    }

    /**
     * @param Category $category
     */
    public function removeCategory(Category $category)
    {
        $this->categories->removeElement($category);
    }

    /**
     * @param Type[]     $types
     * @param Category[] $categories
     */
    public function updateTypesAndCategories(array $types, array $categories)
    {
        $this->updateTypes($types);
        $this->updateCategories($categories);
    }
}
