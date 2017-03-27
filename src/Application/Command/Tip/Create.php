<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Tip;

use Proximum\Vimeet\Domain\Model\Tip\TipTranslation;

class Create
{
    /** @var string */
    public $title;
    
    /** @var bool */
    public $onMeetingManagement;
    
    /** @var bool */
    public $onCatalog;
    
    /** @var bool */
    public $onPrintPlanning;
    
    /** @var TipTranslation[] */
    public $translations;
}
