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
     * MailEventSubscriber constructor.
     *
     * @param SheetInfoGuesser         $sheetInfoGuesser
     * @param AdminRepositoryInterface $adminRepository
     * @param MailerInterface          $mailer
     * @param string                   $sender
     */
    public function __construct(
        SheetInfoGuesser $sheetInfoGuesser,
        AdminRepositoryInterface $adminRepository,
        MailerInterface $mailer,
        $sender
    ) {
        $this->mailer           = $mailer;
        $this->sender           = $sender;
        $this->sheetInfoGuesser = $sheetInfoGuesser;
        $this->adminRepository  = $adminRepository;
    }

    /**
     * @param SheetSubmittedEvent $event
     */
    public function onSheetSubmittedValidation(SheetSubmittedEvent $event)
    {
        $admins            = [];
        $follower          = $event->getSheet()->getFollower();
        $sheetOrganization = $this->sheetInfoGuesser->guessSheetName($event->getSheet(),
            $event->getSheet()->getEvent()->getAvailableLocale($event->getLocale()));

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
                $event->getLocale(),
                $event->getSheet(),
                $admin,
                new DateTime(),
                $sheetOrganization,
                $event->getUser()
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
