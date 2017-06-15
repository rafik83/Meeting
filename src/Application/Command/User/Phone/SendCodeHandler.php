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
use Proximum\Vimeet\Domain\Code\DigitCodeGenerator;
use Proximum\Vimeet\Domain\Messaging\SMS\SMS;
use Proximum\Vimeet\Domain\Model\User\UserEventPhone;
use Proximum\Vimeet\Domain\Repository\User\UserEventPhoneRepositoryInterface;
use Proximum\Vimeet\Domain\User\Phone\ConfirmationCode;

class SendCodeHandler
{
    /** @var UserEventPhoneRepositoryInterface */
    private $userEventPhoneRepository;

    /** @var DigitCodeGenerator */
    private $digitCodeGenerator;

    /** @var SMSSenderInterface */
    private $SMSSender;

    /** @var TranslatorInterface */
    private $translator;

    /** @var DateTimeInterface */
    private $dateTime;

    /**
     * @param UserEventPhoneRepositoryInterface $userEventPhoneRepository
     * @param DigitCodeGenerator                $digitCodeGenerator
     * @param SMSSenderInterface                $SMSSender
     * @param TranslatorInterface               $translator
     * @param DateTimeInterface                 $dateTime
     */
    public function __construct(
        UserEventPhoneRepositoryInterface $userEventPhoneRepository,
        DigitCodeGenerator $digitCodeGenerator,
        SMSSenderInterface $SMSSender,
        TranslatorInterface $translator,
        DateTimeInterface $dateTime
    ) {
        $this->userEventPhoneRepository = $userEventPhoneRepository;
        $this->digitCodeGenerator = $digitCodeGenerator;
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
        $code = $this->digitCodeGenerator->generateCode(ConfirmationCode::CODE_LENGTH);

        $message = $this->translator->trans(
            ConfirmationCode::MESSAGE_TRANSLATION_KEY,
            ['%code%' => $sendCode->phone],
            'messages',
            $sendCode->locale
        );

        $this->SMSSender->send(new SMS($sendCode->phone, $message));

        // Remove eventual previous code for this (user, event)
        $this->userEventPhoneRepository->remove($sendCode->user, $sendCode->event);

        // SMS is successfully sent, persist the code
        $this->userEventPhoneRepository->add(
            new UserEventPhone($sendCode->user, $sendCode->event, $code, $sendCode->phone, $this->dateTime)
        );

        // Set the phone to user sheet(s) profile
        // Set the phone to user account
    }
}
