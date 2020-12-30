<?php

namespace Proximum\Vimeet\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * "Categorie de participant".
 */
class Category implements WhoInterface
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var ArrayCollection
     */
    private $translations;

    /**
     * @var ArrayCollection
     */
    private $types;

    /**
     * @var Event
     */
    private $event;

    /**
     * Category constructor.
     *
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event        = $event;
        $this->types        = new ArrayCollection();
        $this->translations = new ArrayCollection();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get types.
     *
     * @return Type[]
     */
    public function getTypes()
    {
        return $this->types->toArray();
    }

    /**
     * Set Type
     *
     * @param Type $type
     * @param int  $typeId
     *
     * @return Category
     */
    public function setType(Type $type, $typeId)
    {
        $this->types->set($typeId, $type);

        return $this;
    }

    /**
     * @param Type $type
     *
     * @return Category
     */
    public function addType(Type $type)
    {
        $this->types->add($type);

        return $this;
    }

    /**
     * @param Type $type
     *
     * @return Category
     */
    public function removeType(Type $type)
    {
        $this->types->removeElement($type);

        return $this;
    }

    /**
     * Get translations.
     *
     * @return ArrayCollection
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @param string $locale
     *
     * @return string
     */
    public function getTitle($locale)
    {
        return $this->getTranslations()->containsKey($locale) ? $this->getTranslations()->get($locale)->getTitle() : '';
    }

    /**
     * Get event.
     *
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return 'category';
    }

    /**
     * @param string $locale
     * @param string $title
     *
     * @return Category
     */
    public function translate(string $locale, string $title): Category
    {
        if ($this->translations->containsKey($locale)) {
            $this->translations->get($locale)->update($title);
        } else {
            $this->translations->set(
                $locale,
                new CategoryTranslation($this, $locale, $title)
            );
        }

        return $this;
    }
}
