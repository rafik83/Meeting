<?php

namespace Proximum\Vimeet\Domain\Model\Filter;

use Proximum\Vimeet\Domain\Model\Event;

class FilledTemplateFilter
{
    /** @var int */
    private $id;

    /** @var Event */
    private $event;

    /** @var string */
    private $templateKey;

    /** @var string */
    private $label;

    /** @var null|string */
    private $informationType;

    public function __construct(
        Event $event,
        string $templateKey,
        string $label,
        ?string $informationType = null
    ) {
        $this->event = $event;
        $this->templateKey = $templateKey;
        $this->label = $label;
        $this->informationType = $informationType;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getTemplateKey(): string
    {
        return $this->templateKey;
    }

    public function getInformationType(): ?string
    {
        return $this->informationType;
    }
}
