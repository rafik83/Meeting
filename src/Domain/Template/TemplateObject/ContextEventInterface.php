<?php

namespace Proximum\Vimeet\Domain\Template\TemplateObject;

use Proximum\Vimeet\Domain\Model\Event;

interface ContextEventInterface
{
    public function getEvent(): ?Event;
    public function setEvent(Event $event): void;
}
