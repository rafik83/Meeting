<?php

namespace Proximum\Vimeet\Application\Command\PromotionCode;

use Proximum\Vimeet\Domain\Model\Event;

class Create extends AbstractCommand
{
    /**
     * @var Event
     */
    public $event;

    /**
     * Create constructor.
     *
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;

        foreach ($event->getLocales() as $locale) {
            $this->translations[$locale] = [
                'label'       => null,
                'description' => null,
            ];
        }
    }
}
