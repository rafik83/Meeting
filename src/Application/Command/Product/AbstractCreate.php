<?php

namespace Proximum\Vimeet\Application\Command\Product;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;

abstract class AbstractCreate extends AbstractProduct implements Command
{
    /** @var Event */
    public $event;

    /** @var float */
    public $unitPrice;

    /** @var float */
    public $vat;

    /**
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
        $this->vat = $event->getVat();

        foreach ($event->getLocales() as $locale) {
            $this->translations[$locale] = [
                'title'                     => null,
                'heading'                   => null,
                'description'               => null,
                'addon'                     => null,
                'subjectedToValidationHelp' => null,
            ];
        }
    }
}
