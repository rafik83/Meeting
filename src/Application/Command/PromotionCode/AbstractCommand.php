<?php

namespace Proximum\Vimeet\Application\Command\PromotionCode;

use Proximum\Vimeet\Application\Command\Command;

abstract class AbstractCommand implements Command
{
    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $code;

    /**
     * @var \DateTimeInterface
     */
    public $validUntil;

    /**
     * @var int
     */
    public $stock;

    /**
     * @var array
     */
    public $translations = [];

    /**
     * @var array
     */
    public $promotions = [];
}
