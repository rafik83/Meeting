<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Poll;

use Proximum\Vimeet\Application\Query\Happening\Webinar\Poll\PollView;

class VoteResultView
{
    public PollView $pollView;

    public function __construct(PollView $pollView)
    {
        $this->pollView = $pollView;
    }
}
