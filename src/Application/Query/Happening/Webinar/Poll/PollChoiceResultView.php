<?php

namespace Proximum\Vimeet\Application\Query\Happening\Webinar\Poll;

class PollChoiceResultView
{
    public int $id;
    public int $count;

    public function __construct(int $id, int $count)
    {
        $this->id = $id;
        $this->count = $count;
    }
}
