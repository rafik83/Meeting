<?php


namespace Application\Command\Product;


use Symfony\Component\HttpFoundation\File\UploadedFile;

abstract class AbstractPlan
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
    public $availabilityCurrent;

    /**
     * @var int
     */
    public $availabilityMax;

    /**
     * @var array
     */
    public $features = [];

    /**
     * @var array
     */
    public $productIncluded = [];
}
