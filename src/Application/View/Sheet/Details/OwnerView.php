<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
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
    public $shouldBeDisplayed;

    /** @var string */
    public $impersonationUrl;

    /**
     * @param User   $user
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     * @param string $mobile
     * @param string $phone
     * @param bool   $shouldBeDisplayed
     * @param string $impersonationUrl
     */
    public function __construct(
        User $user,
        $firstName,
        $lastName,
        $email,
        $mobile,
        $phone,
        $shouldBeDisplayed,
        string $impersonationUrl
    ) {
        $this->user = $user;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->mobile = $mobile;
        $this->phone = $phone;
        $this->shouldBeDisplayed = $shouldBeDisplayed;
        $this->impersonationUrl = $impersonationUrl;
    }
}
