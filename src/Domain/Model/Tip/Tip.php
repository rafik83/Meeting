<?php

namespace Proximum\Vimeet\Domain\Model\Tip;

use Doctrine\Common\Collections\ArrayCollection;

class Tip
{
    /**
     * Translations keys for visible pages @see AdminBundle/Resources/translations/messages.fr.yml
     */
    const TRANS_VISIBLE_CATALOG = 'admin.tip.column.visible.meeting_management';
    const TRANS_VISIBLE_MEETING_MANAGEMENT = 'admin.tip.column.visible.catalog';
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
    
    /**
     * Tip constructor.
     *
     * @param string            $title
     * @param bool              $onMeetingManagement
     * @param bool              $onCatalog
     * @param bool              $onPrintPlanning
     */
    public function __construct($title, $onMeetingManagement, $onCatalog, $onPrintPlanning)
    {
        $this->title                = $title;
        $this->onMeetingManagement  = $onMeetingManagement;
        $this->onCatalog            = $onCatalog;
        $this->onPrintPlanning      = $onPrintPlanning;
        $this->translations         = new ArrayCollection();
    }
    
    /**
     * Update Tip
     *
     * @param string               $title
     * @param bool                 $onMeetingManagement
     * @param bool                 $onCatalog
     * @param bool                 $onPrintPlanning
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
     * @param array $tipTranslation
     *
     * @return Tip
     */
    public function setTranslation(array $tipTranslation)
    {
        if ($this->translations->contains($tipTranslation)) {
            $this->translations
                ->get($tipTranslation['locale'])
                ->update($tipTranslation['title'], $tipTranslation['content']);
        } else {
            $this->translations->set($tipTranslation['locale'], new TipTranslation(
                $this,
                $tipTranslation['title'],
                $tipTranslation['locale'],
                $tipTranslation['content']
            ));
        }

        return $this;
    }

    /**
     * @param string $locale
     * @param array $tipTranslation
     *
     * @return mixed|null
     */
    public function updateTranslation($locale, array $tipTranslation)
    {
        $this->setTranslation($tipTranslation);

        return $this->translations->remove($locale);
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
     * @return ArrayCollection
     */
    public function getTranslations()
    {
        return $this->translations;
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

        if ($this->onPrintPlanning) {
            $pagesTranslations[] = self::TRANS_VISIBLE_PRINT_PLANNING;
        }

        return $pagesTranslations;
    }
}
