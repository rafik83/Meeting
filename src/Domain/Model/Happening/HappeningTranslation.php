<?php

namespace Proximum\Vimeet\Domain\Model\Happening;

use Proximum\Vimeet\Domain\Model\Happening;

class HappeningTranslation
{
    /** @var int|null */
    private $id;

    /** @var string */
    private $locale;

    /** @var Happening */
    private $happening;

    /** @var string */
    private $title;

    /** @var string|null */
    private $description;

    /**
     * @var string|null
     */
    private $webinarHeaderImage;

    /**
     * @var string|null
     */
    private $webinarWaitingMediaFile;

    /**
     * @var string|null
     */
    private $webinarWaitingMediaType;

    public function __construct(
        Happening $happening,
        string $locale,
        string $title,
        ?string $description,
        ?string $webinarHeaderImage = null,
        ?string $webinarWaitingMediaFile = null,
        ?string $webinarWaitingMediaType = null
    ) {
        $this->happening = $happening;
        $this->locale = $locale;
        $this->title = $title;
        $this->description = $description;
        $this->webinarHeaderImage = $webinarHeaderImage;
        $this->webinarWaitingMediaFile = $webinarWaitingMediaFile;
        $this->webinarWaitingMediaType = $webinarWaitingMediaType;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    public function getHappening(): Happening
    {
        return $this->happening;
    }

    public function setHappening(happening $happening): void
    {
        $this->happening = $happening;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle($title): void
    {
        $this->title = $title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription($description): void
    {
        $this->description = $description;
    }

    public function getWebinarHeaderImage(): ?string
    {
        return $this->webinarHeaderImage;
    }

    public function getWebinarWaitingMediaFile(): ?string
    {
        return $this->webinarWaitingMediaFile;
    }

    public function getWebinarWaitingMediaType(): ?string
    {
        return $this->webinarWaitingMediaType;
    }

    public function update(
        string $title,
        ?string $description,
        ?string $webinarHeaderImage,
        ?string $webinarWaitingMediaFile,
        ?string $webinarWaitingMediaType
    ): void {
        $this->title = $title;
        $this->description = $description;
        $this->webinarHeaderImage = $webinarHeaderImage;
        $this->webinarWaitingMediaFile = $webinarWaitingMediaFile;
        $this->webinarWaitingMediaType = $webinarWaitingMediaType;
    }
}
