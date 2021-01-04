<?php

namespace Proximum\Vimeet\Application\Command\PromotionCode;

abstract class AbstractCommand
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
