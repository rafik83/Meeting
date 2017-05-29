<?php

namespace Proximum\Vimeet\Domain\Model\Tip;

use Doctrine\Common\Collections\ArrayCollection;

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
    public $id;
    
    /**
     * @var string
     */
    public $title;
    
    /**
     * @var ArrayCollection
     */
    public $translations;
    
    /**
     * @var bool
     */
    public $onMeetingManagement;
    
    /**
     * @var bool
     */
    public $onCatalog;
    
    /**
     * @var bool
     */
    public $onPrintPlanning;

    /** @var bool */
    private $onSheet;

    /** @var bool */
    private $onProgram;

    /** @var bool */
    private $onAgenda;

    /**
     * @var \DateTimeInterface
     */
    public $createdAt;

    /**
     * Tip constructor.
     *
     * @param string             $title
     * @param bool               $onMeetingManagement
     * @param bool               $onCatalog
     * @param bool               $onPrintPlanning
     * @param bool               $onSheet
     * @param bool               $onAgenda
     * @param bool               $onProgram
     * @param \DateTimeInterface $createdAt
     */
    public function __construct(
        $title,
        $onMeetingManagement,
        $onCatalog,
        $onPrintPlanning,
        $onSheet,
        $onAgenda,
        $onProgram,
        \DateTimeInterface $createdAt
    ) {
        $this->title                = $title;
        $this->onMeetingManagement  = $onMeetingManagement;
        $this->onCatalog            = $onCatalog;
        $this->onPrintPlanning      = $onPrintPlanning;
        $this->onSheet              = $onSheet;
        $this->onAgenda             = $onAgenda;
        $this->onProgram            = $onProgram;
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
     * @param bool   $onSheet
     * @param bool   $onAgenda
     * @param bool   $onProgram
     *
     * @return Tip
     */
    public function update(
        $title,
        $onMeetingManagement,
        $onCatalog,
        $onPrintPlanning,
        $onSheet,
        $onAgenda,
        $onProgram
    ) {
        $this->title               = $title;
        $this->onMeetingManagement = $onMeetingManagement;
        $this->onCatalog           = $onCatalog;
        $this->onPrintPlanning     = $onPrintPlanning;
        $this->onSheet             = $onSheet;
        $this->onAgenda            = $onAgenda;
        $this->onProgram           = $onProgram;

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
     * @return bool
     */
    public function isOnSheet()
    {
        return $this->onSheet;
    }

    /**
     * @return bool
     */
    public function isOnProgram()
    {
        return $this->onProgram;
    }

    /**
     * @return bool
     */
    public function isOnAgenda()
    {
        return $this->onAgenda;
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
