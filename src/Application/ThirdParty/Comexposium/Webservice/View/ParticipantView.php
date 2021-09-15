<?php

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View;

class ParticipantView
{
    /** @var null|string */
    public $gender;

    /** @var null|string */
    public $firstName;

    /** @var null|string */
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
        ?string $firstName,
        ?string $lastName,
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

    /**
     * @return string
     */
    public function getFullName(): string
    {
        return trim(sprintf('%s %s', (string) $this->firstName, (string) $this->lastName));
    }
}
