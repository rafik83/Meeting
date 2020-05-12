<?php


namespace Proximum\Vimeet\Application\Command\Meeting\Admin;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;

class UpdateVisioSettings implements Command
{
    /** @var Event */
    public $event;

    /** @var array */
    public $localizedVisioHeaders = [];

    public function __construct(Event $event)
    {
        $this->event = $event;

        foreach ($event->getLocales() as $locale) {
            $this->localizedVisioHeaders[$locale] = [
                'header' => null,
                'removeHeader' => false,
            ];
        }
    }
}
