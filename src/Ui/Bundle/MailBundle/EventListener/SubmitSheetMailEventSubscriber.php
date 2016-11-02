<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\EventListener;

use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetSubmittedEvent;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Sheet\SheetSubmittedMail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SubmitSheetMailEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var MailerInterface
     */
    private $mailer;

    /**
     * @var string
     */
    private $sender;

    /**
     * @var SheetInfoGuesser
     */
    private $sheetInfoGuesser;

    /**
     * @var AdminRepositoryInterface
     */
    private $adminRepository;

    /**
     * @var ParticipantInfoGuesser
     */
    private $participantInfoGuesser;

    /**
     * @var \DateTimeInterface
     */
    private $datetime;

    /**
     * MailEventSubscriber constructor.
     *
     * @param SheetInfoGuesser         $sheetInfoGuesser
     * @param ParticipantInfoGuesser   $participantInfoGuesser
     * @param AdminRepositoryInterface $adminRepository
     * @param MailerInterface          $mailer
     * @param string                   $sender
     * @param \DateTimeInterface       $datetime
     */
    public function __construct(
        SheetInfoGuesser $sheetInfoGuesser,
        ParticipantInfoGuesser $participantInfoGuesser,
        AdminRepositoryInterface $adminRepository,
        MailerInterface $mailer,
        $sender,
        \DateTimeInterface $datetime
    ) {
        $this->mailer                 = $mailer;
        $this->sender                 = $sender;
        $this->sheetInfoGuesser       = $sheetInfoGuesser;
        $this->adminRepository        = $adminRepository;
        $this->participantInfoGuesser = $participantInfoGuesser;
        $this->datetime               = $datetime;
    }

    /**
     * @param SheetSubmittedEvent $event
     */
    public function onSheetSubmittedValidation(SheetSubmittedEvent $event)
    {
        $admins    = [];
        $follower = $event->getSheet()->getFollower();
        $locale   = $event->getSheet()->getEvent()->getFallback();

        $sheetName = $this->sheetInfoGuesser->guessSheetName(
            $event->getSheet(),
            $locale
        );

        $participant = $event->getSheet()->getUserParticipant($event->getUser());

        if ($participant === null) {
            $firstName = $event->getUser()->getAccount()->getFirstName();
            $lastName  = $event->getUser()->getAccount()->getLastName();
        } else {
            $firstName = $this->participantInfoGuesser->guessParticipantFirstName($participant, $locale);
            $lastName  = $this->participantInfoGuesser->guessParticipantLastName($participant, $locale);
        }

        if ($follower !== null) {
            $admins[] = $follower;
        } else {
            // notify all organizer and partner allowed to manage this sheet
            $admins = array_merge($admins,
                $this->adminRepository->getAllowedOrganizer(
                    $event->getSheet()->getEvent()
                ),
                $this->adminRepository->getAllowedPartner(
                    $event->getSheet()->getEvent(),
                    $event->getSheet()->getType()
                )
            );
        }

        /** @var Admin $admin */
        foreach ($admins as $admin) {
            $mail = new SheetSubmittedMail(
                $event->getSheet()->getEvent(),
                $this->sender,
                $admin->getEmail(),
                $locale,
                $event->getSheet(),
                $admin,
                $this->datetime,
                $sheetName,
                $firstName,
                $lastName
            );

            $this->mailer->send($mail);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::SHEET_VALIDATION_PENDING => 'onSheetSubmittedValidation',
        ];
    }
}
