<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Tip;

use Doctrine\ORM\PersistentCollection;
use Proximum\Vimeet\Domain\Model\Tip\Tip;

class Update
{
    /** @var Tip */
    public $tip;
    
    /** @var string */
    public $title;
    
    /** @var bool */
    public $onMeetingManagement;
    
    /** @var bool */
    public $onCatalog;
    
    /** @var bool */
    public $onPrintPlanning;
    
    /** @var PersistentCollection */
    public $translations;
    
    /**
     * Update constructor.
     *
     * @param Tip $tip
     */
    public function __construct(Tip $tip)
    {
        $this->tip                 = $tip;
        $this->title               = $tip->getTitle();
        $this->onMeetingManagement = $tip->isOnMeetingManagement();
        $this->onPrintPlanning     = $tip->isOnPrintPlanning();
        $this->onCatalog           = $tip->isOnCatalog();
        $this->translations        = $tip->getTranslations();
    }
}
