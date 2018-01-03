<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Happening;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Happening\Category;
use Proximum\Vimeet\Domain\Model\Type;

class AbstractHappeningCommand implements Command
{
    /** @var Category */
    public $category;

    /** @var \DateTimeInterface */
    public $begin;

    /** @var \DateTimeInterface */
    public $end;

    /** @var Type[] */
    public $types = [];

    /** @var array */
    public $translations = [];

    /** @var array */
    public $talkings = [];

    /** @var bool */
    public $questionAllowed;

    /** @var int|null */
    public $limitParticipant;
}
