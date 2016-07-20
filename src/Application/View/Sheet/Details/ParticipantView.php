<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Sheet\Details;

use Proximum\Vimeet\Domain\Template\TemplateData;

class ParticipantView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var TemplateData
     */
    public $templateData;

    /**
     * @var bool
     */
    public $isOwner;

    /**
     * @param int          $id
     * @param TemplateData $templateData
     * @param bool         $isOwner
     */
    public function __construct($id, TemplateData $templateData, $isOwner = false)
    {
        $this->id           = $id;
        $this->templateData = $templateData;
        $this->isOwner      = $isOwner;
    }
}
