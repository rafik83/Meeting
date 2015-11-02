<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

class TypeTemplatesView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $participantTemplate;

    /**
     * @var string
     */
    public $sheetTemplate;

    /**
     * @var string
     */
    public $packageTemplate;

    /**
     * @param integer $id
     * @param string  $participantTemplate
     * @param string  $sheetTemplate
     * @param string  $packageTemplate
     */
    public function __construct($id, $participantTemplate, $sheetTemplate, $packageTemplate)
    {
        $this->id                  = $id;
        $this->participantTemplate = $participantTemplate;
        $this->sheetTemplate       = $sheetTemplate;
        $this->packageTemplate     = $packageTemplate;
    }
}
