<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Application\View\Happening\Webinar\StreamDTO;
use Proximum\Vimeet\Domain\Model\Happening;

class StreamCommand implements Command
{
    /** @var Happening */
    public $happening;

    /** @var StreamDTO */
    public $stream;

    public function __construct(
        Happening $happening,
        StreamDTO $stream
    ) {
        $this->happening = $happening;
        $this->stream = $stream;
    }
}
