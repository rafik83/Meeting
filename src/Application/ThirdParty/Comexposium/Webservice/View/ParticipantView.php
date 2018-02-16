<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View;

class ParticipantView
{
    /** @var null|string */
    public $gender;

    /** @var string */
    public $firstName;

    /** @var string */
    public $lastName;

    /** @var string */
    public $email;

    /** @var null|string */
    public $userCompanyName;

    /** @var string */
    public $locale;

    /** @var null|string */
    public $phone;

    /** @var ParticipantPositionView[] */
    public $participantPositionViews;

    public function __construct(
        ?string $gender,
        string $firstName,
        string $lastName,
        string $email,
        string $locale,
        ?string $phone,
        ?string $userCompanyName,
        array $participantPositionViews
    ) {
        $this->gender = $gender;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->locale = $locale;
        $this->phone = $phone;
        $this->participantPositionViews = $participantPositionViews;
        $this->userCompanyName = $userCompanyName;
    }
}
