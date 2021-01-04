<?php

namespace Proximum\Vimeet\Application\Command\StaticFormulation;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;

class Create implements Command
{
    /** @var Event */
    public $event;

    /** @var array */
    public $translations;

    /** @var string */
    public $key;

    /** @var Type[] */
    public $types;

    public function __construct(Event $event, string $key, array $titles)
    {
        $this->event = $event;
        $this->key = $key;

        foreach ($titles as $locale => $title) {
            $this->translations[$locale] = [
                'title' => $title
            ];
        }
    }
}
