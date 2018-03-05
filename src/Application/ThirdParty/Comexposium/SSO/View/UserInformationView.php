<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\View;

class UserInformationView
{
    /** @var string */
    public $email;

    /** @var null|string */
    public $civility;

    /** @var null|string */
    public $firstname;

    /** @var null|string */
    public $lastname;

    /** @var null|string */
    public $mobilePhone;

    /** @var null|string */
    public $country;

    public function __construct(
        string $email,
        ?string $civility,
        ?string $firstname,
        ?string $lastname,
        ?string $mobilePhone,
        ?string $country
    ) {
        $this->civility = $civility;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->mobilePhone = $mobilePhone;
        $this->country = $country;
        $this->email = $email;
    }
}
