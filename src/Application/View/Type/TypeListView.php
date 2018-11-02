<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Type;

class TypeListView
{
    /** @var int */
    public $id;

    /** @var int */
    public $position;

    /** @var string */
    public $title;

    /** @var bool */
    public $hidden;

    /** @var string */
    public $registrationTemplate;

    /** @var string */
    public $sheetTemplate;

    /** @var string */
    public $package;

    /** @var bool */
    public $hasSpecificPaymentConditions;

    /** @var bool */
    public $hasSpecificTermsOfSale;

    /**
     * @param int    $id
     * @param int    $position
     * @param string $title
     * @param bool   $hidden
     * @param string $registrationTemplate
     * @param string $sheetTemplate
     * @param string $package
     * @param bool   $hasSpecificPaymentConditions
     * @param bool   $hasSpecificTermsOfSale
     */
    public function __construct(
        $id,
        $position,
        $title,
        $hidden,
        $registrationTemplate,
        $sheetTemplate,
        $package,
        bool $hasSpecificPaymentConditions = false,
        bool $hasSpecificTermsOfSale = false
    ) {
        $this->id                           = $id;
        $this->position                     = $position;
        $this->title                        = $title;
        $this->hidden                       = $hidden;
        $this->registrationTemplate         = $registrationTemplate;
        $this->sheetTemplate                = $sheetTemplate;
        $this->package                      = $package;
        $this->hasSpecificPaymentConditions = $hasSpecificPaymentConditions;
        $this->hasSpecificTermsOfSale = $hasSpecificTermsOfSale;
    }
}
