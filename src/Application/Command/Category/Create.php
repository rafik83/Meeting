<?php

namespace Proximum\Vimeet\Application\Command\Category;

use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Event;

class Create
{
    /**
     * @var Category
     */
    public $category;

    /**
     * @var Event
     */
    public $event;

    /**
     * @var array
     */
    public $translations = [];

    /**
     * @var array
     */
    public $types = [];

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
                'title' => '',
            ];
        }
    }
}
