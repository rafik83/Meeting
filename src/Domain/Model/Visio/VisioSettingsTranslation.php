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

    /** @var null|string */
    private $endSound;

    /** @var null|string */
    private $endImage;

    /** @var null|string */
    private $endMessage;

    public function __construct(
        VisioSettings $visioSettings,
        string $locale,
        ?string $header = null,
        ?string $endSound = null,
        ?string $endImage = null,
        ?string $endMessage = null
    ) {
        $this->visioSettings = $visioSettings;
        $this->locale = $locale;
        $this->header = $header;
        $this->endSound = $endSound;
        $this->endImage = $endImage;
        $this->endMessage = $endMessage;
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

    public function getEndSound(): ?string
    {
        return $this->endSound;
    }

    public function getEndImage(): ?string
    {
        return $this->endImage;
    }

    public function getEndMessage(): ?string
    {
        return $this->endMessage;
    }

    public function update(
        ?string $header,
        ?string $endSound = null,
        ?string $endImage = null,
        ?string $endMessage = null
    ): void {
        $this->header = $header;
        $this->endSound = $endSound;
        $this->endImage = $endImage;
        $this->endMessage = $endMessage;
    }
}
