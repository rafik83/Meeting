<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\User\Phone;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
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

    /** @var bool */
    public $fromModal;

    /** @var null|Sheet */
    public $sheet;

    /** @var null|Participant */
    public $participant;

    /**
     * @param Request          $request
     * @param User             $user
     * @param Event            $event
     * @param null|string      $mobileNumberToValidate
     * @param bool             $ignorePhoneAlreadyValidated
     * @param bool             $fromModal
     * @param null|Sheet       $sheet
     * @param null|Participant $participant
     */
    public function __construct(
        Request $request,
        User $user,
        Event $event,
        ?string $mobileNumberToValidate = null,
        bool $ignorePhoneAlreadyValidated = false,
        bool $fromModal = false,
        ?Sheet $sheet = null,
        ?Participant $participant = null
    ) {
        $this->request = $request;
        $this->user = $user;
        $this->event = $event;
        $this->ignorePhoneAlreadyValidated = $ignorePhoneAlreadyValidated;
        $this->mobileNumberToValidate = $mobileNumberToValidate;
        $this->fromModal = $fromModal;
        $this->sheet = $sheet;
        $this->participant = $participant;
    }
}
