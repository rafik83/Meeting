<?php


namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Question;


use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\User;

class DeleteHappeningQuestion implements Command
{
    /** @var int */
    public $messageId;

    /** @var User */
    public $user;

    /** @var Happening */
    public $happening;

    public function __construct(
        int $messageId,
        User $user,
        Happening $happening
    ) {
        $this->messageId = $messageId;
        $this->user = $user;
        $this->happening = $happening;
    }
}
