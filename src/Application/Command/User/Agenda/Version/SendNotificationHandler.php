<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\User\Agenda\Version;

use Proximum\Vimeet\Application\Adapter\SMSSenderInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Exception\Messaging\SMS\FailToSendSMSException;
use Proximum\Vimeet\Application\Exception\User\Agenda\Version\UserPhoneNotAvailableException;
use Proximum\Vimeet\Domain\Messaging\SMS\SMS;
use Proximum\Vimeet\Domain\Model\Event\EventUrlGeneratorInterface;
use Proximum\Vimeet\Domain\Repository\User\UserEventPhoneRepositoryInterface;
use Proximum\Vimeet\Domain\User\Agenda\Version\DiffVerbalizer;

class SendNotificationHandler
{
    const EVENT_AGENDA_ROUTE = 'event_agenda';

    /** @var UserEventPhoneRepositoryInterface */
    private $userEventPhoneRepository;

    /** @var DiffVerbalizer */
    private $diffVerbalizer;

    /** @var SMSSenderInterface */
    private $SMSSender;

    /** @var TranslatorInterface */
    private $translator;

    /** @var EventUrlGeneratorInterface */
    private $eventUrlGenerator;

    /**
     * @param DiffVerbalizer                    $diffVerbalizer
     * @param SMSSenderInterface                $SMSSender
     * @param TranslatorInterface               $translator
     * @param UserEventPhoneRepositoryInterface $userEventPhoneRepository
     * @param EventUrlGeneratorInterface        $eventUrlGenerator
     */
    public function __construct(
        DiffVerbalizer $diffVerbalizer,
        SMSSenderInterface $SMSSender,
        TranslatorInterface $translator,
        UserEventPhoneRepositoryInterface $userEventPhoneRepository,
        EventUrlGeneratorInterface $eventUrlGenerator
    ) {
        $this->userEventPhoneRepository = $userEventPhoneRepository;
        $this->diffVerbalizer = $diffVerbalizer;
        $this->SMSSender = $SMSSender;
        $this->translator = $translator;
        $this->eventUrlGenerator = $eventUrlGenerator;
    }

    /**
     * @param SendNotification $command
     *
     * @throws FailToSendSMSException
     * @throws UserPhoneNotAvailableException
     */
    public function handle(SendNotification $command)
    {
        $userEventPhone = $this->userEventPhoneRepository->findValidated($command->user, $command->event);

        if (null === $userEventPhone) {
            throw new UserPhoneNotAvailableException();
        }

        $startingSentence = $this->translator->trans(
            DiffVerbalizer::TRANSLATION_AGENDA_MODIFIED,
            [],
            DiffVerbalizer::TRANSLATION_DOMAIN,
            $command->user->getLocale()
        );
        $verbalizedDiff = $this->diffVerbalizer->verbalizeDiff(
            $command->currentVersion,
            $command->diff,
            $command->user->getLocale()
        );

        $agendaUrl = $this->eventUrlGenerator->generateEventAbsoluteUrl(
            $command->event,
            self::EVENT_AGENDA_ROUTE,
            ['sheet' => $command->sheet->getId(),
             '_locale' => $command->event->getAvailableLocale($command->user->getLocale())]
        );

        $message = $startingSentence . "\n" . $verbalizedDiff . "\n" . $agendaUrl;

        $sms = new SMS($userEventPhone->getPhone(), $message);
        $this->SMSSender->send($sms);
    }
}
