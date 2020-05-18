<?php


namespace Proximum\Vimeet\Application\Command\Visio;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Visio\VisioSettings;

class UpdateVisioSettings implements Command
{
    /** @var Event */
    public $event;

    /** @var array */
    public $localizedVisioSettings = [];

    /** @var VisioSettings */
    public $visioSettings;

    public function __construct(Event $event, VisioSettings $visioSettings)
    {
        $this->event = $event;
        $this->visioSettings = $visioSettings;

        foreach ($event->getLocales() as $locale) {
            $hasHeader = $visioSettings->hasHeader($locale);
            $this->localizedVisioSettings[$locale] = [
                'header' => null,
                'removeHeader' => false,
                'hasHeader' => $hasHeader,
            ];
        }
    }
}
