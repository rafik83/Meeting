<?php

namespace Proximum\Vimeet\Application\View\Happening\Webinar;

class StreamDTO
{
    /** @var string */
    public $streamId;

    /** @var string */
    public $type;

    /** @var string */
    public $action;

    public function __construct(
        string $streamId,
        string $type,
        string $action
    ) {
        $this->streamId = $streamId;
        $this->type = $type;
        $this->action = $action;
    }
}
