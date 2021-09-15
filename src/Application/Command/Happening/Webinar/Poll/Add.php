<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Poll;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\User;

class Add implements Command
{
    public Happening $happening;
    public User $user;
    public string $title;
    public array $choices;
    public bool $multipleChoice;
    public bool $publish;

    /**
     * @param string[] $choices
     */
    public function __construct(
        Happening $happening,
        User $user,
        string $title,
        array $choices,
        bool $multipleChoice,
        bool $publish
    ) {
        $this->happening = $happening;
        $this->user = $user;
        $this->title = $title;
        $this->choices = $choices;
        $this->multipleChoice = $multipleChoice;
        $this->publish = $publish;
    }
}
