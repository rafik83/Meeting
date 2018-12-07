<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Transactional\Mail;

use Proximum\Vimeet\Application\Components\Mail\AbstractMail;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Prepare\PrepareActivateAccountMail;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Prepare\PrepareOrderConfirmedMail;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Prepare\PrepareParticipantAddedMail;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Prepare\PreparePreRegisterMail;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Prepare\PrepareRegisterAccountMail;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Prepare\PrepareTransactionConfirmMail;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Prepare\PrepareUserCompleteProfileMail;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\AbstractPrepareMail;
use Proximum\Vimeet\Domain\Transactional\Mail\Constant;

class PrepareHandler
{
    /** @var PrepareRegisterAccountMail */
    private $prepareRegisterAccountMail;

    /** @var PrepareActivateAccountMail */
    private $prepareActivateAccountMail;

    /** @var PrepareParticipantAddedMail */
    private $prepareParticipantAddedMail;

    /** @var PreparePreRegisterMail */
    private $preparePreRegisterMail;

    /** @var PrepareUserCompleteProfileMail */
    private $prepareUserCompleteProfileMail;

    /** @var PrepareTransactionConfirmMail */
    private $prepareTransactionConfirmMail;

    /** @var PrepareOrderConfirmedMail */
    private $prepareOrderConfirmedMail;

    public function __construct(
        PrepareRegisterAccountMail $prepareRegisterAccountMail,
        PrepareActivateAccountMail $prepareActivateAccountMail,
        PrepareParticipantAddedMail $prepareParticipantAddedMail,
        PreparePreRegisterMail $preparePreRegisterMail,
        PrepareUserCompleteProfileMail $prepareUserCompleteProfileMail,
        PrepareTransactionConfirmMail $prepareTransactionConfirmMail,
        PrepareOrderConfirmedMail $prepareOrderConfirmedMail
    ) {
        $this->prepareRegisterAccountMail = $prepareRegisterAccountMail;
        $this->prepareActivateAccountMail = $prepareActivateAccountMail;
        $this->prepareParticipantAddedMail = $prepareParticipantAddedMail;
        $this->preparePreRegisterMail = $preparePreRegisterMail;
        $this->prepareUserCompleteProfileMail = $prepareUserCompleteProfileMail;
        $this->prepareTransactionConfirmMail = $prepareTransactionConfirmMail;
        $this->prepareOrderConfirmedMail = $prepareOrderConfirmedMail;
    }

    public function handle(AbstractPrepareMail $prepareMail): ?AbstractMail
    {
        switch ($prepareMail->type) {
            case Constant::TRANSACTIONAL_MAIL_KEY_USER_REGISTERED:
                return $this->prepareRegisterAccountMail->prepare($prepareMail);
            case Constant::TRANSACTIONAL_MAIL_KEY_USER_ACTIVATE_ACCOUNT:
                return $this->prepareActivateAccountMail->prepare($prepareMail);
            case Constant::TRANSACTIONAL_MAIL_KEY_PARTICIPANT_ADDED_CONFIRMATION:
                return $this->prepareParticipantAddedMail->prepare($prepareMail);
            case Constant::TRANSACTIONAL_MAIL_KEY_PRE_REGISTERED:
                return $this->preparePreRegisterMail->prepare($prepareMail);
            case Constant::TRANSACTIONAL_MAIL_KEY_USER_COMPLETE_PROFILE:
                return $this->prepareUserCompleteProfileMail->prepare($prepareMail);
            case Constant::TRANSACTIONAL_MAIL_KEY_TRANSACTION_CONFIRMED:
                return $this->prepareTransactionConfirmMail->prepare($prepareMail);
            case Constant::TRANSACTIONAL_MAIL_KEY_ORDER_CONFIRMED:
                return $this->prepareOrderConfirmedMail->prepare($prepareMail);
            case Constant::TRANSACTIONAL_MAIL_KEY_AGENDA_VERSION_DIFF_CHANGED:
                return $this->prepareOrderConfirmedMail->prepare($prepareMail);
            default: return null;
        }
    }
}
