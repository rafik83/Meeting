<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Command;

use Proximum\Vimeet\Application\ThirdParty\LENI\View\LeniUserView;
use Proximum\Vimeet\Domain\Model\Event\ExtraParameter;

class LeniApiCall
{
    /** @var LeniUserView */
    public $leniUserView;

    /** @var ExtraParameter */
    public $leniUserParameter;

    /** @var ExtraParameter */
    public $leniUserEvent;

    /**
     * @param LeniUserView   $leniUserView
     * @param ExtraParameter $leniUserParameter
     * @param ExtraParameter $leniUserEvent
     */
    public function __construct(
        LeniUserView $leniUserView = null,
        ExtraParameter $leniUserParameter,
        ExtraParameter $leniUserEvent
    ) {
        $this->leniUserView = $leniUserView;
        $this->leniUserParameter = $leniUserParameter;
        $this->leniUserEvent = $leniUserEvent;
    }
}
