<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Planner;

class TypePriorityView
{
    /** @var TypeView */
    public $fromType;

    /** @var TypeView */
    public $toType;

    /** @var int */
    public $priority;

    /**
     * @param TypeView $fromType
     * @param TypeView $toType
     * @param int      $priority
     */
    public function __construct(TypeView $fromType, TypeView $toType, $priority)
    {
        $this->fromType = $fromType;
        $this->toType   = $toType;
        $this->priority = $priority;
    }

    /**
     * @return int
     */
    public function getFromType()
    {
        return $this->fromType->id;
    }

    /**
     * @return int
     */
    public function getToType()
    {
        return $this->toType->id;
    }
}
