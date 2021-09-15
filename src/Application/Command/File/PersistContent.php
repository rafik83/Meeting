<?php

namespace Proximum\Vimeet\Application\Command\File;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;

class PersistContent implements Command
{
    /** @var Event */
    public $event;

    /** @var string */
    public $content;

    /** @var string */
    public $filenamePattern;

    public function __construct(Event $event, string $content, string $filenamePattern)
    {
        if (2 !== substr_count($filenamePattern, '%s')) {
            throw new \InvalidArgumentException('filenamePattern must contain two placeholder (%s)');
        }

        $this->event = $event;
        $this->content = $content;
        $this->filenamePattern = $filenamePattern;
    }
}
