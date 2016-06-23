<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Product\Option;


use Symfony\Component\HttpFoundation\File\UploadedFile;

abstract class AbstractOption
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var array
     */
    public $translations = [];

    /**
     * @var UploadedFile
     */
    public $file;

    /**
     * @var float
     */
    public $unitPrice;

    /**
     * @var int
     */
    public $quantityMax;

    /**
     * @var int
     */
    public $availabilityCurrent;

    /**
     * @var int
     */
    public $availabilityMax;

    /**
     * @var bool
     */
    public $updatable;

    /**
     * @var \DateTimeInterface|null
     */
    public $updatableUntil;

    /**
     * @var bool
     */
    public $subjectedToValidation;
}