<?php

namespace Proximum\Vimeet\Application\Query\Tip\Condition;

use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQuery;
use Proximum\Vimeet\Application\View\Tip\Event\TipTranslationView;
use Proximum\Vimeet\Domain\UserEvent\UserEventPhoneChecker;

class PhoneConfirmationCondition implements ConditionInterface
{
    /** @var UserEventPhoneChecker */
    private $userEventPhoneChecker;

    public function __construct(UserEventPhoneChecker $userEventPhoneChecker)
    {
        $this->userEventPhoneChecker = $userEventPhoneChecker;
    }

    public function isSatisfiedBy(TipTranslationViewQuery $query, TipTranslationView $tipTranslationView): bool
    {
        if (null === $tipTranslationView->conditionIsPhoneConfirmed) {
            return true;
        }

        $phoneConfirmed = $this->userEventPhoneChecker->isValidated($query->user, $query->event);

        if (true === $tipTranslationView->conditionIsPhoneConfirmed) {
            return $phoneConfirmed;
        }

        return !$phoneConfirmed;
    }
}
