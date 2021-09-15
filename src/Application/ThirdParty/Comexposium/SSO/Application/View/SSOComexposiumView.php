<?php

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\View;

class SSOComexposiumView
{
    /** @var string */
    public $salon;

    /** @var string */
    public $sessionSalon;

    /** @var string */
    public $application;

    /** @var string */
    public $locale;

    /** @var null|string */
    public $comexposiumSSOLoaderLibEndpoint;

    public function __construct(
        string $salon,
        string $sessionSalon,
        string $application,
        string $locale,
        ?string $comexposiumSSOLoaderLibEndpoint
    ) {
        $this->salon = $salon;
        $this->sessionSalon = $sessionSalon;
        $this->application = $application;
        $this->locale = $locale;
        $this->comexposiumSSOLoaderLibEndpoint = $comexposiumSSOLoaderLibEndpoint;
    }
}
