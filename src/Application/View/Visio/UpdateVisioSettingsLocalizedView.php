<?php

namespace Proximum\Vimeet\Application\View\Visio;

class UpdateVisioSettingsLocalizedView
{
    /** @var string */
    public $locale;

    /** @var string|null */
    public $header;

    /** @var string|null */
    public $endSound;

    /** @var string|null */
    public $endImage;

    /** @var string|null */
    public $endMessage;

    public function __construct(
        string $locale,
        ?string $header = null,
        ?string $endSound = null,
        ?string $endImage = null,
        ?string $endMessage = null
    ) {
        $this->locale = $locale;
        $this->header = $header;
        $this->endSound = $endSound;
        $this->endImage = $endImage;
        $this->endMessage = $endMessage;
    }

    public function hasHeader(): bool
    {
        return null !== $this->header;
    }

    public function hasEndSound(): bool
    {
        return null !== $this->endSound;
    }

    public function hasEndImage(): bool
    {
        return null !== $this->endImage;
    }
}
