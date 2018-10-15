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
use Proximum\Vimeet\Application\Components\Transactional\Mail\Prepare\PrepareParticipantAddedMail;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Prepare\PrepareRegisterAccountMail;
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

    public function __construct(
        PrepareRegisterAccountMail $prepareRegisterAccountMail,
        PrepareActivateAccountMail $prepareActivateAccountMail,
        PrepareParticipantAddedMail $prepareParticipantAddedMail
    ) {
        $this->prepareRegisterAccountMail = $prepareRegisterAccountMail;
        $this->prepareActivateAccountMail = $prepareActivateAccountMail;
        $this->prepareParticipantAddedMail = $prepareParticipantAddedMail;
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
            default: return null;
        }
    }
}
