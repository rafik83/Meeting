<?php

namespace Proximum\Vimeet\Domain\Model\Happening;

class PollChoice
{
    private int $id;
    private Poll $poll;
    private string $content;

    public function __construct(Poll $poll, string $content)
    {
        $this->poll = $poll;
        $this->content = $content;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPoll(): Poll
    {
        return $this->poll;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
