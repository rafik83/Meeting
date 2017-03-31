<?php

namespace Proximum\Vimeet\Domain\Model\Tip;

use Doctrine\Common\Collections\ArrayCollection;

class Tip
{
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
        $translations = array_unique($tipTranslation, SORT_REGULAR);

        while ($this->translations->count() > count($translations)) {
            $this->translations->removeElement($this->translations->last());
        }

        foreach ($tipTranslation as $locale => $translation) {
            if ($this->translations->get($locale)) {
                $this->translations->get($locale)->update($translation['title'], $translation['content']);
            } else {
                $this->translations->add(
                    new TipTranslation(
                        $this,
                        $tipTranslation['title'],
                        $tipTranslation['locale'],
                        $tipTranslation['content']
                    )
                );
            }
        }

        return $this;
    }

    /**
     * @param $locale
     * @param $title
     * @param $content
     */
    public function updateTranslation($locale, $title, $content)
    {
        $this->translations->get($locale)->update($title, $content);
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
}
