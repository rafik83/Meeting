<?php

namespace Proximum\Vimeet\Domain\Model\Tip;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\Model\Type;

class Tip
{
    /**
     * Translations keys for visible pages @see AdminBundle/Resources/translations/messages.fr.yml
     */
    const TRANS_VISIBLE_CATALOG = 'admin.tip.column.visible.catalog';
    const TRANS_VISIBLE_MEETING_MANAGEMENT = 'admin.tip.column.visible.meeting_management';
    const TRANS_VISIBLE_PRINT_PLANNING = 'admin.tip.column.visible.print_planning';

    /**
     * @var int
     */
    private $id;
    
    /**
     * @var string
     */
    private $title;

    /**
     * @var ArrayCollection
     */
    private $translations;

    /**
     * @var ArrayCollection
     */
    private $types;
    
    /**
     * @var bool
     */
    private $onMeetingManagement;
    
    /**
     * @var bool
     */
    private $onCatalog;
    
    /**
     * @var bool
     */
    private $onPrintPlanning;

    /**
     * @var \DateTimeInterface
     */
    private $createdAt;

    /**
     * Tip constructor.
     *
     * @param string             $title
     * @param bool               $onMeetingManagement
     * @param bool               $onCatalog
     * @param bool               $onPrintPlanning
     * @param \DateTimeInterface $createdAt
     */
    public function __construct($title, $onMeetingManagement, $onCatalog, $onPrintPlanning, \DateTimeInterface $createdAt)
    {
        $this->title                = $title;
        $this->onMeetingManagement  = $onMeetingManagement;
        $this->onCatalog            = $onCatalog;
        $this->onPrintPlanning      = $onPrintPlanning;
        $this->translations         = new ArrayCollection();
        $this->createdAt            = $createdAt;
    }
    
    /**
     * Update Tip
     *
     * @param string $title
     * @param bool   $onMeetingManagement
     * @param bool   $onCatalog
     * @param bool   $onPrintPlanning
     *
     * @return Tip
     */
    public function update(
        $title,
        $onMeetingManagement,
        $onCatalog,
        $onPrintPlanning
    ) {
        $this->title               = $title;
        $this->onMeetingManagement = $onMeetingManagement;
        $this->onCatalog           = $onCatalog;
        $this->onPrintPlanning     = $onPrintPlanning;

        return $this;
    }

    /**
     * @param string $locale
     * @param string $title
     * @param string $content
     *
     * @return Tip   $this
     */
    public function translate($locale, $title, $content)
    {
        if ($this->hasTranslation($locale)) {
            $this->getTranslation($locale)->set($locale, $title, $content);
        } else {
            $this->setTranslation($locale, $title, $content);
        }

        return $this;
    }

    /**
     * @param string $locale
     *
     * @return string|null
     */
    public function getTranslationTitle($locale)
    {
        return $this->hasTranslation($locale) ? $this->getTranslation($locale)->getTitle() : null;
    }

    /**
     * @param string $locale
     *
     * @return string|null
     */
    public function getTranslationContent($locale)
    {
        return $this->hasTranslation($locale) ? $this->getTranslation($locale)->getContent() : null;
    }

    /**
     * @param string $locale
     * @param string $title
     * @param string $content
     */
    public function setTranslation($locale, $title, $content)
    {
        $this->translations->set(
            $locale,
            new TipTranslation(
                $this,
                new \DateTime(),
                $title,
                $locale,
                $content
            )
        );
    }

    /**
     * @param string $locale
     *
     * @return bool
     */
    public function hasTranslation($locale)
    {
        return $this->translations->containsKey($locale);
    }

    /**
     * @param string $locale
     *
     * @return TipTranslation
     */
    public function getTranslation($locale)
    {
        return $this->translations->get($locale);
    }

    /**
     * @param string $locale
     */
    public function removeTranslation($locale)
    {
        $this->translations->remove($locale);
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
     * @param Type $type
     *
     * @return bool
     */
    public function hasType(Type $type)
    {
        return $this->types->contains($type->getId());
    }

    /**
     * @param Type $type
     *
     * @return Type|null
     */
    public function getType(Type $type)
    {
        return $this->types->get($type->getId());
    }
    
    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return TipTranslation[]
     */
    public function getTranslations()
    {
        return $this->translations->toArray();
    }

    /**
     * @return Type[]
     */
    public function getTypes()
    {
        return $this->types->toArray();
    }
    
    /**
     * @return bool
     */
    public function isOnMeetingManagement()
    {
        return $this->onMeetingManagement;
    }
    
    /**
     * @return bool
     */
    public function isOnCatalog()
    {
        return $this->onCatalog;
    }
    
    /**
     * @return bool
     */
    public function isOnPrintPlanning()
    {
        return $this->onPrintPlanning;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return array
     */
    public function getPagesTranslations()
    {
        $pagesTranslations = [];

        if ($this->isOnCatalog()) {
            $pagesTranslations[] = self::TRANS_VISIBLE_CATALOG;
        }

        if ($this->isOnMeetingManagement()) {
            $pagesTranslations[] = self::TRANS_VISIBLE_MEETING_MANAGEMENT;
        }

        if ($this->isOnPrintPlanning()) {
            $pagesTranslations[] = self::TRANS_VISIBLE_PRINT_PLANNING;
        }

        return $pagesTranslations;
    }
}
