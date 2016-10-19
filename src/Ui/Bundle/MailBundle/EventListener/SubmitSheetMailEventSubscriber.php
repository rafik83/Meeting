<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\EventListener;

use DateTime;
use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetSubmittedEvent;
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
     * MailEventSubscriber constructor.
     *
     * @param SheetInfoGuesser         $sheetInfoGuesser
     * @param ParticipantInfoGuesser   $participantInfoGuesser
     * @param AdminRepositoryInterface $adminRepository
     * @param MailerInterface          $mailer
     * @param string                   $sender
     */
    public function __construct(
        SheetInfoGuesser $sheetInfoGuesser,
        ParticipantInfoGuesser $participantInfoGuesser,
        AdminRepositoryInterface $adminRepository,
        MailerInterface $mailer,
        $sender
    ) {
        $this->mailer                 = $mailer;
        $this->sender                 = $sender;
        $this->sheetInfoGuesser       = $sheetInfoGuesser;
        $this->adminRepository        = $adminRepository;
        $this->participantInfoGuesser = $participantInfoGuesser;
    }

    /**
     * @param SheetSubmittedEvent $event
     */
    public function onSheetSubmittedValidation(SheetSubmittedEvent $event)
    {
        $admins    = [];
        $firstname = null;
        $lastname  = null;

        $follower = $event->getSheet()->getFollower();
        $locale   = $event->getSheet()->getEvent()->getFallback();

        $sheetName = $this->sheetInfoGuesser->guessSheetName(
            $event->getSheet(),
            $locale
        );

        $participant = $event->getSheet()->getUserParticipant($event->getUser());

        if ($participant === null) {
            $firstname = $event->getUser()->getAccount()->getFirstName();
            $lastname  = $event->getUser()->getAccount()->getLastName();
        } else {
            $firstname = $this->participantInfoGuesser->guessParticipantFirstName($participant, $locale);
            $lastname  = $this->participantInfoGuesser->guessParticipantLastName($participant, $locale);
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

        foreach ($admins as $admin) {
            $mail = new SheetSubmittedMail(
                $event->getSheet()->getEvent(),
                $this->sender,
                $admin->getEmail(),
                $locale,
                $event->getSheet(),
                $admin,
                new DateTime(),
                $sheetName,
                $firstname,
                $lastname
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
