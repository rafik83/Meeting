<?php

namespace Proximum\Vimeet\Application\Command\Product;

use Symfony\Component\HttpFoundation\File\UploadedFile;

abstract class AbstractProduct
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

    /** @var bool */
    public $canScanParticipant;

    /**
     * @var \DateTimeInterface
     */
    public $deletableUntil;

    /**
     * @var \DateTimeInterface
     */
    public $buyableUntil;

    /**
     * @var null|UploadedFile
     */
    public $file;

    /**
     * @var bool
     */
    public $subjectedToValidation;

    /**
     * @var array
     */
    public $features = [];

    /**
     * @var array
     */
    public $productIncluded = [];
}
