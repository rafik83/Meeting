<?php

namespace Proximum\Vimeet\Application\Query\Sheet\Detail\Participant;

use Proximum\Vimeet\Application\View\Sheet\Details\Participant\PhoneNotValidatedView;
use Proximum\Vimeet\Application\View\Sheet\Details\Participant\PhoneValidatedView;
use Proximum\Vimeet\Application\View\Sheet\Details\Participant\PhoneValidationStatusView;
use Proximum\Vimeet\Domain\UserEvent\UserEventPhoneChecker;

class PhoneValidationStatusQueryHandler
{
    /** @var UserEventPhoneChecker */
    private $checker;

    /**
     * @param UserEventPhoneChecker $checker
     */
    public function __construct(UserEventPhoneChecker $checker)
    {
        $this->checker = $checker;
    }

    /**
     * @param PhoneValidationStatusQuery $query
     *
     * @return PhoneValidationStatusView
     */
    public function handle(PhoneValidationStatusQuery $query): PhoneValidationStatusView
    {
        $userEventPhone = $this->checker->getValidatedUserEventPhone(
            $query->participant->getUser(),
            $query->participant->getSheet()->getEvent()
        );

        return null !== $userEventPhone ? new PhoneValidatedView() : new PhoneNotValidatedView();
    }
}
