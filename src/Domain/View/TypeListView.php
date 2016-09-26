<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\View;

class TypeListView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $position;

    /**
     * @var string
     */
    public $title;

    /**
     * @var bool
     */
    public $hidden;

    /**
     * @var string
     */
    public $registrationTemplate;

    /**
     * @var string
     */
    public $sheetTemplate;

    /**
     * @var string
     */
    public $package;

    /**
     * TypeListView constructor.
     *
     * @param int    $id
     * @param int    $position
     * @param string $title
     * @param bool   $hidden
     * @param string $registrationTemplate
     * @param string $sheetTemplate
     * @param string $package
     */
    public function __construct(
        $id,
        $position,
        $title,
        $hidden,
        $registrationTemplate,
        $sheetTemplate,
        $package
    ) {
        $this->id                   = $id;
        $this->position             = $position;
        $this->title                = $title;
        $this->hidden               = $hidden;
        $this->registrationTemplate = $registrationTemplate;
        $this->sheetTemplate        = $sheetTemplate;
        $this->packageTemplate      = $package;
    }
}
