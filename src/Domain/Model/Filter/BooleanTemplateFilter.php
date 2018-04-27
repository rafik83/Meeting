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

class BooleanTemplateFilter
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Event
     */
    private $event;

    /**
     * Key is a reserved mysql word
     * Therefore, the variable is TemplateKey
     *
     * @var string
     */
    private $templateKey;

    /**
     * @var string
     */
    private $label;

    /**
     * @param Event  $event
     * @param string $templateKey
     * @param string $label
     */
    public function __construct(Event $event, $templateKey, $label)
    {
        $this->event       = $event;
        $this->templateKey = $templateKey;
        $this->label       = $label;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getTemplateKey()
    {
        return $this->templateKey;
    }
}
