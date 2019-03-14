<?php

namespace Proximum\Vimeet\Application\View\Happening\Admin;

class SpeakerExportView
{
    /** @var string */
    private $name;

    /** @var string */
    private $position;

    /** @var string */
    private $society;

    /** @var string|null */
    private $urlLogo;

    /** @var string|null */
    private $urlAvatar;

    /**
     * @param string $name
     * @param string $position
     * @param string $society
     * @param string $urlLogo
     * @param string $urlAvatar
     */
    public function __construct(string $name, string $position, string $society, ?string $urlLogo, ?string $urlAvatar)
    {
        $this->name = $name;
        $this->position = $position;
        $this->society = $society;
        $this->urlLogo = $urlLogo;
        $this->urlAvatar = $urlAvatar;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPosition(): string
    {
        return $this->position;
    }

    /**
     * @return string
     */
    public function getSociety(): string
    {
        return $this->society;
    }

    /**
     * @return string|null
     */
    public function getUrlLogo(): ?string
    {
        return $this->urlLogo;
    }

    /**
     * @return string|null
     */
    public function getUrlAvatar(): ?string
    {
        return $this->urlAvatar;
    }
}
