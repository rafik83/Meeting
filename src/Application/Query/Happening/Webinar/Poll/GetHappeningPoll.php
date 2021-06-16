<?php

namespace Proximum\Vimeet\Application\Query\Happening\Webinar\Poll;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Happening;

class GetHappeningPoll implements Query
{
    public Happening\Poll $poll;
    public bool $addResults;

    public function __construct(Happening\Poll $poll, bool $addResults)
    {
        $this->poll = $poll;
        $this->addResults = $addResults;
    }
}
