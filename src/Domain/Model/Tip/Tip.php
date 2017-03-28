<?php

namespace Proximum\Vimeet\Domain\Model\Tip;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;

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
     * @param PersistentCollection $translations
     *
     * @return Tip
     */
    public function update(
        $title,
        $onMeetingManagement,
        $onCatalog,
        $onPrintPlanning,
        PersistentCollection $translations
    ) {
        $this->title               = $title;
        $this->onMeetingManagement = $onMeetingManagement;
        $this->onCatalog           = $onCatalog;
        $this->onPrintPlanning     = $onPrintPlanning;
        $this->translations        = $translations;
        
        return $this;
    }
    
    /**
     * @param $locale
     * @param $title
     * @param $content
     *
     * @return $this
     */
    public function addTranslation($locale, $title, $content)
    {
        if ($this->translations->containsKey($locale)) {
            $this->translations->get($locale)->update($title, $content);
        } else {
            $this->translations->set($locale, new TipTranslation($this, $locale, $title, $content));
        }
    
        return $this;
    }
    
    /**
     * @param TipTranslation $translation
     */
    public function removeTranslation(TipTranslation $translation)
    {
        $this->translations->removeElement($translation);
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
