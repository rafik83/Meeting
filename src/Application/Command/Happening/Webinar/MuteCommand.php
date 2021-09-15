<?php


namespace Proximum\Vimeet\Application\Command\Happening\Webinar;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Happening;

class MuteCommand implements Command
{
    public Happening $happening;

    public int $userId;

    public function __construct(
        Happening $happening,
        int $userId
    ) {
        $this->happening = $happening;
        $this->userId = $userId;
    }
}
