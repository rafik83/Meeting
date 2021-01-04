<?php

namespace Proximum\Vimeet\Domain\User\Phone;

use Proximum\Vimeet\Domain\Event\Day\DDayGuesser;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Tip\ConfirmationPhoneTipChecker;
use Proximum\Vimeet\Domain\UserEvent\UserEventPhoneChecker;

class ValidationRequiredChecker
{
    /** @var DDayGuesser */
    private $dDayGuesser;

    /** @var ConfirmationPhoneTipChecker */
    private $confirmationPhoneTipChecker;

    /** @var UserEventPhoneChecker */
    private $userEventPhoneChecker;

    /**
     * ValidationRequiredChecker constructor.
     *
     * @param DDayGuesser                 $dDayGuesser
     * @param ConfirmationPhoneTipChecker $confirmationPhoneTipChecker
     * @param UserEventPhoneChecker       $userEventPhoneChecker
     */
    public function __construct(
        DDayGuesser $dDayGuesser,
        ConfirmationPhoneTipChecker $confirmationPhoneTipChecker,
        UserEventPhoneChecker $userEventPhoneChecker
    ) {
        $this->dDayGuesser                 = $dDayGuesser;
        $this->confirmationPhoneTipChecker = $confirmationPhoneTipChecker;
        $this->userEventPhoneChecker       = $userEventPhoneChecker;
    }

    /**
     * @param Sheet $sheet
     * @param User  $user
     *
     * @return bool
     */
    public function handle(Sheet $sheet, User $user): bool
    {
        if ($this->dDayGuesser->isItDDayAndFeatureEnabled($sheet->getEvent())) {
            $isTipConfirmationPhoneEnabled = $this->confirmationPhoneTipChecker->isEnabled(
                $sheet->getEvent(),
                $sheet->getType()
            );

            if ($isTipConfirmationPhoneEnabled) {
                return !$this->userEventPhoneChecker->isValidated(
                    $user,
                    $sheet->getEvent()
                );
            }

            return false;
        }

        return false;
    }
}
