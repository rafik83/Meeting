<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\User\Phone;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\HttpFoundation\Request;

class SendCodeForm
{
    /** @var Request */
    public $request;

    /** @var User */
    public $user;

    /** @var Event */
    public $event;

    /** @var bool */
    public $ignorePhoneAlreadyValidated;

    /** @var string */
    public $mobileNumberToValidate;

    /** @var null|string */
    public $actionRoute;

    /**
     * @param Request     $request
     * @param User        $user
     * @param Event       $event
     * @param null|string $actionRoute
     * @param null|string $mobileNumberToValidate
     * @param bool        $ignorePhoneAlreadyValidated
     */
    public function __construct(
        Request $request,
        User $user,
        Event $event,
        ?string $actionRoute = null,
        ?string $mobileNumberToValidate = null,
        bool $ignorePhoneAlreadyValidated = false
    ) {
        $this->request                     = $request;
        $this->user                        = $user;
        $this->event                       = $event;
        $this->ignorePhoneAlreadyValidated = $ignorePhoneAlreadyValidated;
        $this->mobileNumberToValidate      = $mobileNumberToValidate;
        $this->actionRoute                 = $actionRoute;
    }
}
