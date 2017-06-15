<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\User\Phone;

use DateTimeInterface;
use Proximum\Vimeet\Application\Adapter\SMSSenderInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Exception\Messaging\SMS\FailToSendSMSException;
use Proximum\Vimeet\Application\Exception\Messaging\SMS\InvalidReceiverException;
use Proximum\Vimeet\Domain\Repository\User\UserEventPhoneRepositoryInterface;

class SendCodeHandler
{
    const SEND_CODE_MESSAGE_TRANSLATE_KEY = 'to define';

    /** @var UserEventPhoneRepositoryInterface */
    private $userEventPhoneRepository;

    /** @var SMSSenderInterface */
    private $SMSSender;

    /** @var TranslatorInterface */
    private $translator;

    /** @var DateTimeInterface */
    private $dateTime;

    /**
     * @param UserEventPhoneRepositoryInterface $userEventPhoneRepository
     * @param SMSSenderInterface                $SMSSender
     * @param TranslatorInterface               $translator
     * @param DateTimeInterface                 $dateTime
     */
    public function __construct(
        UserEventPhoneRepositoryInterface $userEventPhoneRepository,
        SMSSenderInterface $SMSSender,
        TranslatorInterface $translator,
        DateTimeInterface $dateTime
    ) {
        $this->userEventPhoneRepository = $userEventPhoneRepository;
        $this->translator = $translator;
        $this->SMSSender = $SMSSender;
        $this->dateTime = $dateTime;
    }

    /**
     * @param SendCode $sendCode
     *
     * @throws FailToSendSMSException
     * @throws InvalidReceiverException
     */
    public function handle(SendCode $sendCode)
    {
    }
}
