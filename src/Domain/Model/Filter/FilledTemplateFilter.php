<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

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

    public function __construct(Event $event, string $templateKey, string $label)
    {
        $this->event       = $event;
        $this->templateKey = $templateKey;
        $this->label       = $label;
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
}
