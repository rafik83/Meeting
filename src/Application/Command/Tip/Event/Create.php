<?php

namespace Proximum\Vimeet\Application\Command\Tip\Event;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Tip\Tip;

class Create extends AbstractEventTip
{
    /** @var Event */
    public $event;

    public function __construct(Event $event)
    {
        $this->event = $event;
        $this->display = Tip::DISPLAY_DEFAULT;

        foreach ($this->event->getLocales() as $locale) {
            $this->translations[$locale] = [
                'title' => '',
                'content' => '',
            ];
        }
    }
}
