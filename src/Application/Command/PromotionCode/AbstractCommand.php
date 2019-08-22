<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

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
