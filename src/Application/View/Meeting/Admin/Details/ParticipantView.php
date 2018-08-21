<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Meeting\Admin\Details;

class ParticipantView
{
    /** @var string */
    public $fullName;

    /** @var null|string */
    public $mobile;

    /**
     * @param string $fullName
     * @param null|string $mobile
     */
    public function __construct($fullName, $mobile)
    {
        $this->fullName = $fullName;
        $this->mobile = $mobile;
    }
}
