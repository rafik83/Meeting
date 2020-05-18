<?php

namespace Proximum\Vimeet\Application\View\Visio;

class UpdateVisioSettingsLocalizedView
{
    /** @var string */
    public $locale;

    /** @var string|null */
    public $header;

    public function __construct(
        string $locale,
        ?string $header = null
    ) {
        $this->locale = $locale;
        $this->header = $header;
    }

    public function hasHeader(): bool
    {
        return null !== $this->header;
    }
}
