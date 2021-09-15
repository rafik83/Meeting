<?php

namespace Proximum\Vimeet\Domain\Model\Happening;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\Model\AbstractCategory;
use Proximum\Vimeet\Domain\Model\Event;

class Category extends AbstractCategory
{
    /**
     * @var int
     */
    private $rank;

    /**
     * @var ArrayCollection
     */
    protected $translations;

    /**
     * Category constructor.
     *
     * @param Event  $event
     * @param string $picto
     * @param int    $rank
     * @param string $leftColor
     * @param string $rightColor
     */
    public function __construct(Event $event, $picto, $rank, $leftColor, $rightColor)
    {
        parent::__construct($event, $picto, $leftColor, $rightColor);

        $this->rank         = $rank;
        $this->translations = new ArrayCollection();
    }

    /**
     * Get rank
     *
     * @return int
     */
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * Set rank
     *
     * @param int $rank
     *
     * @return Category
     */
    public function setRank($rank)
    {
        $this->rank = $rank;

        return $this;
    }

    /**
     * @param string $locale
     * @param string $title
     */
    public function update($locale, $title)
    {
        $this->translations->containsKey($locale)
            ? $this->translations->get($locale)->setTitle($title)
            : $this->translations->set($locale, new CategoryTranslation($this, $locale, $title));
    }

    /**
     * @param CategoryTranslation $categoryTranslation
     */
    public function setTranslation(CategoryTranslation $categoryTranslation)
    {
        $this->translations->set($categoryTranslation->getLocale(), $categoryTranslation);
    }

    /**
     * @return array
     */
    public function getTranslations()
    {
        return $this->translations->toArray();
    }

    /**
     * @param string $locale
     *
     * @return string
     */
    public function getTitle($locale)
    {
        return $this->translations->containsKey($locale)
            ? $this->translations->get($locale)->getTitle()
            : '';
    }
}
