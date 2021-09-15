<?php

namespace Proximum\Vimeet\Application\Command\Event\Participant;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;

class AddFastCheckin implements Command
{
    /** @var Event */
    public $event;

    /** @var string */
    public $email;

    /** @var string */
    public $firstname;

    /** @var string */
    public $lastname;

    /** @var string */
    public $mobile;

    /** @var string */
    public $sheetTitle;

    /** @var Type|null */
    public $type;

    /** @var string */
    public $country;

    /** @var bool */
    public $hasAccessToMeetings = false;

    /** @var bool */
    public $isUserKnown = false;

    public function __construct(Event $event, string $email, ?User $user)
    {
        $this->event = $event;
        $this->email = $email;

        if ($user instanceof User) {
            $this->firstname = $user->getFirstName();
            $this->lastname = $user->getLastName();
            $this->mobile = $user->getMobile();
            $this->country = $user->getCountry();
            $this->isUserKnown = true;
        }
    }
}
