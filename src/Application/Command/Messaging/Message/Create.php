<?php

namespace Proximum\Vimeet\Application\Command\Messaging\Message;

use Proximum\Vimeet\Domain\Model\Event;

final class Create
{
    /** @var string */
    public $name;

    /** @var array */
    public $translations;

    /** @var Event */
    private $event;

    /**
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;

        foreach ($event->getLocales() as $locale) {
            $this->translations[$locale] = ['locale' => $locale];
        }
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }
}
