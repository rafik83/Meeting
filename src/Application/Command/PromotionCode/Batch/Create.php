<?php

namespace Proximum\Vimeet\Application\Command\PromotionCode\Batch;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;

class Create implements Command
{
    /** @var string */
    public $title;

    /** @var null|string */
    public $prefix;

    /** @var \DateTimeInterface */
    public $validUntil;

    /** @var int */
    public $number = 1;

    /** @var null|int */
    public $stock;

    /** @var array */
    public $translations = [];

    /** @var array */
    public $promotions = [];

    /** @var Event */
    public $event;

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
