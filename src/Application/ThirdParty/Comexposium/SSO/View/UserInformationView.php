<?php

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\View;

class UserInformationView
{
    /** @var string */
    public $email;

    /** @var null|string */
    public $gender;

    /** @var null|string */
    public $firstname;

    /** @var null|string */
    public $lastname;

    /** @var null|string */
    public $mobilePhone;

    /** @var null|string */
    public $country;

    /** @var string */
    public $locale;

    public function __construct(
        string $email,
        ?string $gender,
        ?string $firstname,
        ?string $lastname,
        ?string $mobilePhone,
        ?string $country,
        string $locale
    ) {
        $this->gender = $gender;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->mobilePhone = $mobilePhone;
        $this->country = $country;
        $this->email = $email;
        $this->locale = $locale;
    }
}
