<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Catalog;

class AvailabilityConfirmationCheckerView
{
    const ALLOWED_TO_ACCESS = 'allowed';
    const REDIRECT = 'redirect';

    /** @var string */
    public $type;

    /** @var null|string */
    public $redirectRoute;

    /**
     * @param string      $type
     * @param null|string $redirectRoute
     */
    public function __construct(string $type, ?string $redirectRoute)
    {
        $this->type = $type;
        $this->redirectRoute = $redirectRoute;
    }

    /**
     * @return bool
     */
    public function isAllowedToAccess(): bool
    {
        return self::ALLOWED_TO_ACCESS === $this->type;
    }
}
