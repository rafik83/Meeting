<?php


namespace Proximum\Vimeet\Application\Command\Happening;


use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class EvaluateHappening implements Command
{
    /** @var User */
    public $user;

    /** @var Happening */
    public $happening;

    /** @var int|null */
    public $evaluation;

    /** @var Sheet */
    public $sheet;

    /** @var Event */
    public $event;

    public function __construct(
        Event $event,
        Sheet $sheet,
        Happening $happening,
        User $user,
        ?int $evaluation
    ) {
        $this->happening = $happening;
        $this->sheet = $sheet;
        $this->evaluation = null;
        $this->event = $event;
        $this->user = $user;
        $this->evaluation = $evaluation;
    }
}
