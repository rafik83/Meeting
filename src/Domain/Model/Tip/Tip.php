<?php

namespace Proximum\Vimeet\Domain\Model\Tip;

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
     * @var TipTranslation[]
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
     * @param TipTranslation[]  $translations
     * @param bool              $onMeetingManagement
     * @param bool              $onCatalog
     * @param bool              $onPrintPlanning
     */
    public function __construct($title, array $translations, $onMeetingManagement, $onCatalog, $onPrintPlanning)
    {
        $this->title                = $title;
        $this->translations         = $translations;
        $this->onMeetingManagement  = $onMeetingManagement;
        $this->onCatalog            = $onCatalog;
        $this->onPrintPlanning      = $onPrintPlanning;
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
