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
     * @var string|null
     */
    public $mobile;

    /**
     * @var string|null
     */
    public $phone;

    /**
     * @var bool
     */
    public $shouldBeDisplayed;

    public function __construct(
        User $user,
        string $firstName,
        string $lastName,
        string $email,
        ?string $mobile,
        ?string $phone,
        bool $shouldBeDisplayed
    ) {
        $this->user = $user;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->mobile = $mobile;
        $this->phone = $phone;
        $this->shouldBeDisplayed = $shouldBeDisplayed;
    }
}
