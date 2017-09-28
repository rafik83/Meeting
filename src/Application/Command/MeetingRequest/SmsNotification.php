<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\MeetingRequest;

use Proximum\Vimeet\Application\Adapter\SMSSenderInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Domain\Messaging\SMS\SMS;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\EventUrlGeneratorInterface;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class SmsNotification
{
    /** @var SMSSenderInterface */
    private $SMSSender;

    /** @var TranslatorInterface */
    private $translator;

    /** @var EventUrlGeneratorInterface */
    private $eventUrlGenerator;

    /**
     * @param SMSSenderInterface         $SMSSender
     * @param TranslatorInterface        $translator
     * @param EventUrlGeneratorInterface $eventUrlGenerator
     */
    public function __construct(
        SMSSenderInterface $SMSSender,
        TranslatorInterface $translator,
        EventUrlGeneratorInterface $eventUrlGenerator
    ) {
        $this->SMSSender    = $SMSSender;
        $this->translator   = $translator;
        $this->eventUrlGenerator = $eventUrlGenerator;
    }

    /**
     * @param Sheet $sheet
     * @param Event $event
     * @param User  $user
     * @param int   $countPendingMeetingRequest
     */
    public function sendSms(Sheet $sheet, Event $event, User $user, int $countPendingMeetingRequest): void
    {
        $locale  = $event->getAvailableLocale($user->getLocale());
        $message = $this->buildMessage($event, $sheet, $countPendingMeetingRequest, $locale);

        $this->SMSSender->send(
            new SMS($user->getMobile(), $message)
        );
    }

    /**
     * Build message like
     *
     * Les Rendez-Vous Carnot: vous avez reçu X propositions de RDVs,
     * voir https://gdr => lien vers GDR de la fiche "Propositions reçues" filtrées sur les disponibles
     *
     * @param Event  $event
     * @param Sheet  $sheet
     * @param int    $countPendingMeetingRequest
     * @param string $locale
     *
     * @return string
     */
    private function buildMessage(
        Event $event,
        Sheet $sheet,
        int $countPendingMeetingRequest,
        string $locale
    ): string {
        $meetingRequestManagementUrl = $this->eventUrlGenerator->generateEventAbsoluteUrl(
            $event,
            'event_meeting_list_request',
            array_merge(
                ['sheet' => $sheet->getId()],
                ['state' => 'receive'],
                ['_locale' => $locale]
            )
        );

        return $this
            ->translator
            ->trans(
                'sms.reminder.pending_meeting_request',
                [
                    '%eventTitle%'                  => $event->getTitle(),
                    '%countPendingMeetingRequest%'  => $countPendingMeetingRequest,
                    '%meetingRequestManagementUrl%' => $meetingRequestManagementUrl,
                ],
                null,
                $locale
            );
    }
}
