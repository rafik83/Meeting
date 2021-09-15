<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Poll;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\User;

class Vote implements Command
{
    public int $pollId;
    public Happening $happening;
    public User $user;
    public array $choicesId;

    public function __construct($pollId, Happening $happening, User $user, array $choicesId)
    {
        $this->pollId = $pollId;
        $this->happening = $happening;
        $this->user = $user;
        $this->choicesId = $choicesId;
    }
}
