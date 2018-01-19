<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Application\View\Package\ParticipantProductView;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class Add
{
    /** @var Sheet */
    public $sheet;

    /** @var string */
    public $locale;

    /** @var string */
    public $firstName;

    /** @var string */
    public $lastName;

    /** @var string */
    public $email;

    /** @var bool */
    public $owner;

    /** @var User */
    public $adder;

    /**
     * ParticipantProductView selected to add the new participant
     * This product can be null as the package is not always passable
     *
     * @var ParticipantProductView|null
     */
    public $product;

    /** @var bool */
    public $needToSelectProduct;

    /**
     * @param Sheet                       $sheet
     * @param string                      $locale
     * @param User                        $adder
     * @param ParticipantProductView|null $product
     * @param bool                        $needToSelectProduct
     */
    public function __construct(
        Sheet $sheet,
        $locale,
        User $adder,
        ParticipantProductView $product = null,
        bool $needToSelectProduct = false
    ) {
        $this->sheet               = $sheet;
        $this->locale              = $locale;
        $this->owner               = false;
        $this->adder               = $adder;
        $this->product             = $product;
        $this->needToSelectProduct = $needToSelectProduct;
    }
}
