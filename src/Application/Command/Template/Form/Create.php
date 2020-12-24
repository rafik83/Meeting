<?php

namespace Proximum\Vimeet\Application\Command\Template\Form;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;

class Create implements Command
{
    /** @var Event */
    public $event;

    /** @var string */
    public $title;

    /** @var array */
    public $translations;

    public function __construct(Event $event)
    {
        $this->event = $event;
        $this->translations = [];

        foreach ($event->getLocales() as $locale) {
            $this->translations[$locale] = [
                'title' => '',
            ];
        }
    }
}
