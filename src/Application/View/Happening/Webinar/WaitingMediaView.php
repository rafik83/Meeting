<?php

namespace Proximum\Vimeet\Application\View\Happening\Webinar;

class WaitingMediaView
{
    /** @var string|null */
    public $file;

    /** @var string|null */
    public $type;

    public function __construct(?string $file, ?string $type)
    {
        $this->file = $file;
        $this->type = $type;
    }
}
