<?php

namespace Proximum\Vimeet\Application\Command\Happening\Category;

use Proximum\Vimeet\Application\Command\Command;

abstract class AbstractCategory implements Command
{
    /** @var int */
    public $rank;

    /** @var string */
    public $picto;

    /** @var array */
    public $translations = [];

    /** @var string */
    public $leftColor;

    /** @var string */
    public $rightColor;
}
