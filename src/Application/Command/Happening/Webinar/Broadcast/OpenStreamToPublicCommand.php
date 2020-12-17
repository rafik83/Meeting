<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Broadcast;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Happening;

class OpenStreamToPublicCommand implements Command
{
    /** @var Happening */
    public $happening;

    /** @var string|null */
    public $mediaSharingType;

    /** @var string|null */
    public $mediaSharingStream;

    public function __construct(Happening $happening, ?string $mediaSharingType, ?string $mediaSharingStream)
    {
        $this->happening = $happening;
        $this->mediaSharingType = $mediaSharingType;
        $this->mediaSharingStream = $mediaSharingStream;
    }
}
