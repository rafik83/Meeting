<?php


namespace Proximum\Vimeet\Domain\Model\Visio;

class VisioSettingsTranslation
{
    /** @var null|int */
    private $id;

    /** @var VisioSettings */
    private $visioSettings;

    /** @var string */
    private $locale;

    /** @var string|null */
    private $header;

    public function __construct(
        VisioSettings $visioSettings,
        string $locale,
        ?string $header = null
    ) {
        $this->visioSettings = $visioSettings;
        $this->locale = $locale;
        $this->header = $header;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVisioSettings(): VisioSettings
    {
        return $this->visioSettings;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getHeader(): ?string
    {
        return $this->header;
    }

    public function update(?string $header): void
    {
        $this->header = $header;
    }
}
