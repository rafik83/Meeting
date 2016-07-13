<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Sheet\Details;

use Proximum\Vimeet\Domain\Model\User;

class OwnerView
{
    /**
     * @var User
     */
    public $user;

    /**
     * @var string
     */
    public $firstName;

    /**
     * @var string
     */
    public $lastName;

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $mobile;

    /**
     * @var string
     */
    public $phone;

    /**
     * @var bool
     */
    public $shouldBeDisplay;

    /**
     * @param User   $user
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     * @param string $mobile
     * @param string $phone
     * @param bool   $shouldBeDisplay
     */
    public function __construct(User $user, $firstName, $lastName, $email, $mobile, $phone, $shouldBeDisplay = true)
    {
        $this->user            = $user;
        $this->firstName       = $firstName;
        $this->lastName        = $lastName;
        $this->email           = $email;
        $this->mobile          = $mobile;
        $this->phone           = $phone;
        $this->shouldBeDisplay = $shouldBeDisplay;
    }
}
