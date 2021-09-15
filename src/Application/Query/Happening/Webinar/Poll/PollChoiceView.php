<?php

namespace Proximum\Vimeet\Application\Query\Happening\Webinar\Poll;

class PollChoiceView
{
    public int $id;
    public string $content;
    public ?int $votesPercent;

    public function __construct(int $id, string $content, ?int $votesPercent)
    {
        $this->id = $id;
        $this->content = $content;
        $this->votesPercent = $votesPercent;
    }
}
