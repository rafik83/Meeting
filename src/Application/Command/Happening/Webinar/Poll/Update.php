<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Poll;

use Proximum\Vimeet\Application\Command\Command;

class Update implements Command
{
    public int $pollId;
    public int $happeningId;
    public string $title;
    public array $choices;
    public bool $multipleChoice;
    public bool $publish;

    public function __construct(
        int $pollId,
        int $happeningId,
        string $title,
        array $choices,
        bool $multipleChoice,
        bool $publish
    ) {
        $this->pollId = $pollId;
        $this->happeningId = $happeningId;
        $this->title = $title;
        $this->choices = $choices;
        $this->multipleChoice = $multipleChoice;
        $this->publish = $publish;
    }
}
