<?php

namespace Proximum\Vimeet\Application\Command\Happening\Category;

use Proximum\Vimeet\Domain\Model\Event;

class Create extends AbstractCategory
{
    /** @var Event */
    public $event;

    /**
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event      = $event;
        $this->leftColor  = $event->getConfiguration()->getLeftColor();
        $this->rightColor = $event->getConfiguration()->getRightColor();

        foreach ($event->getLocales() as $locale) {
            $this->translations[$locale] = [
                'title' => '',
            ];
        }
    }
}
