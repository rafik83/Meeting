<?php

namespace Proximum\Vimeet\Application\Query\Happening\Webinar\Poll;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Happening;

class GetHappeningPollResults implements Query
{
    public Happening\Poll $poll;

    public function __construct(Happening\Poll $poll)
    {
        $this->poll = $poll;
    }
}
