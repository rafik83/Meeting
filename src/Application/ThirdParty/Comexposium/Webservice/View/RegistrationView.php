<?php

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View;

use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\Nomenclature\NomenclatureItemView;

class RegistrationView
{
    /** @var null|string */
    public $reference;

    /** @var null|string */
    public $companyName;

    /** @var null|string */
    public $status;

    /** @var null|string */
    public $address;

    /** @var null|string */
    public $zipCode;

    /** @var null|string */
    public $city;

    /** @var null|string ISO 3166-1 alpha-2 country code */
    public $country;

    /** @var null|string */
    public $companyPhone;

    /** @var null|string */
    public $webSite;

    /** @var ParticipantView */
    public $participantView;

    /** @var NomenclatureItemView[] */
    public $nomenclatureItemViews;

    /** @var RegistrationDescriptionView[] */
    public $registrationDescriptionViews;

    public function __construct(
        ?string $reference,
        ?string $companyName,
        ?string $status,
        ?string $address,
        ?string $zipCode,
        ?string $city,
        ?string $country,
        ?string $companyPhone,
        ?string $webSite,
        ParticipantView $participantView,
        array $nomenclatureItemViews,
        array $registrationDescriptionViews
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
        $this->nomenclatureItemViews = $nomenclatureItemViews;
        $this->registrationDescriptionViews = $registrationDescriptionViews;
    }
}
