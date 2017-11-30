<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Tip\Event;

use Proximum\Vimeet\Domain\Model\Type;

abstract class AbstractEventTip
{
    /** @var string */
    public $title;

    /** @var Type[] */
    public $types;

    /** @var bool */
    public $onMeetingManagement;

    /** @var bool */
    public $onCatalog;

    /** @var bool */
    public $onPrintPlanning;

    /** @var bool */
    public $onSheet;

    /** @var bool */
    public $onProgram;

    /** @var bool */
    public $onAgenda;

    /** @var bool */
    public $onConfirmationPhone;

    /** @var array */
    public $translations;
}
