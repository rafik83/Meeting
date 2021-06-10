<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Poll;

use Proximum\Vimeet\Application\Command\Command;

class UpdateStatus implements Command
{
    public int $pollId;
    public string $status;

    public function __construct(
        int $pollId,
        string $status
    ) {
        $this->pollId = $pollId;
        $this->status = $status;
    }
}
