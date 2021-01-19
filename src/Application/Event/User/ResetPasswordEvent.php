<?php

namespace Proximum\Vimeet\Application\Event\User;

use Proximum\Vimeet\Domain\Model\Event as EventModel;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\ForgottenPasswordToken;
use Symfony\Component\EventDispatcher\Event;

class ResetPasswordEvent extends Event
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var EventModel
     */
    private $event;

    /**
     * @var ForgottenPasswordToken
     */
    private $forgottenPasswordToken;

    /**
     * @var string
     */
    private $locale;

    /** @var bool */
    private $requestedByAdmin;

    public function __construct(
        User $user,
        EventModel $event,
        ForgottenPasswordToken $forgottenPasswordToken,
        string $locale,
        bool $requestedByAdmin = false
    ) {
        $this->user = $user;
        $this->event = $event;
        $this->forgottenPasswordToken = $forgottenPasswordToken;
        $this->locale = $locale;
        $this->requestedByAdmin = $requestedByAdmin;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return EventModel
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return ForgottenPasswordToken
     */
    public function getForgottenPasswordToken()
    {
        return $this->forgottenPasswordToken;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    public function isRequestedByAdmin(): bool
    {
        return $this->requestedByAdmin;
    }
}
