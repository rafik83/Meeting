<?php

namespace Proximum\Vimeet\Domain\Model\Catalog\External;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
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

    /** @var bool */
    private $hasMessage;

    /** @var string */
    private $registrationUrl;

    /**
     * @var ArrayCollection of CatalogVisibilityTranslation
     *
     * @see CatalogVisibilityTranslation
     */
    private $messageTranslations;

    /**
     * CatalogVisibility constructor.
     *
     * @param Event $event
     * @param bool  $hasMessage
     */
    public function __construct(Event $event, bool $hasMessage = false)
    {
        $this->event               = $event;
        $this->types               = new ArrayCollection();
        $this->categories          = new ArrayCollection();
        $this->messageTranslations = new ArrayCollection();
        $this->hasMessage          = $hasMessage;
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
     * @return Type[]
     */
    public function getTypes(): array
    {
        return $this->types->toArray();
    }

    /**
     * @return Category[]
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

    /**
     * @param bool $state
     *
     * @return CatalogVisibility
     */
    public function enableMessage(bool $state)
    {
        $this->hasMessage = $state;

        return $this;
    }

    /**
     * @param string $title
     * @param string $content
     * @param string $locale
     *
     * @return $this
     */
    public function translate($title, $content, string $locale)
    {
        if ($this->messageTranslations->containsKey($locale)) {
            $this->messageTranslations->get($locale)->update($title, $content);
        } else {
            $this->messageTranslations->set($locale, new CatalogVisibilityTranslation(
                $this, $title, $content, $locale
            ));
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function hasMessage(): bool
    {
        return $this->hasMessage;
    }

    /**
     * @param string $locale
     *
     * @return CatalogVisibilityTranslation|null
     */
    public function getMessage(string $locale): ?CatalogVisibilityTranslation
    {
        return $this->messageTranslations->containsKey($locale) ?
            $this->messageTranslations->get($locale) :
            null;
    }

    /**
     * @return ArrayCollection|PersistentCollection
     */
    public function getMessageTranslations()
    {
        return $this->messageTranslations;
    }

    /**
     * @return null|string
     */
    public function getRegistrationUrl(): ?string
    {
        return $this->registrationUrl;
    }

    /**
     * @param null|string $registrationUrl
     */
    public function setRegistrationUrl(?string $registrationUrl)
    {
        $this->registrationUrl = $registrationUrl;
    }
}
