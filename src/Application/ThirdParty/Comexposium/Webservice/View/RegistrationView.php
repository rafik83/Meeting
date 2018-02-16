<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View;

class RegistrationView
{
    /** @var string */
    public $reference;

    /** @var string */
    public $companyName;

    /** @var string */
    public $status;

    /** @var string */
    public $address;

    /** @var string */
    public $zipCode;

    /** @var string */
    public $city;

    /** @var null|string ISO 3166-1 alpha-2 country code */
    public $country;

    /** @var string */
    public $companyPhone;

    /** @var string */
    public $webSite;

    /** @var ParticipantView */
    public $participantView;

    /** @var array */
    public $nomenclatureReferences;

    public function __construct(
        string $reference,
        string $companyName,
        string $status,
        string $address,
        string $zipCode,
        string $city,
        ?string $country,
        string $companyPhone,
        string $webSite,
        ParticipantView $participantView,
        array $nomenclatureReferences
    ) {
        $this->reference = $reference;
        $this->companyName = $companyName;
        $this->status = $status;
        $this->address = $address;
        $this->zipCode = $zipCode;
        $this->city = $city;
        $this->country = $country;
        $this->companyPhone = $companyPhone;
        $this->webSite = $webSite;
        $this->participantView = $participantView;
        $this->nomenclatureReferences = $nomenclatureReferences;
    }
}
