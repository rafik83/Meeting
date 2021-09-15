<?php

namespace Proximum\Vimeet\Application\Command\Unavailability\Mass;

use Proximum\Vimeet\Domain\Model\Event;

class Create extends Base
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @param Event          $event
     * @param Event\Day|null $day
     */
    public function __construct(Event $event, Event\Day $day = null)
    {
        $this->event        = $event;
        $this->blocking     = true;
        $this->translations = [];

        if (null !== $day) {
            $this->begin = $day->getStartTime();
            $this->end   = $day->getStartTime();
        }

        foreach ($this->event->getLocales() as $locale) {
            $this->translations[$locale] = [
                'title'       => '',
                'description' => '',
            ];
        }
    }
}
