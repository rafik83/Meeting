<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\EventListener;

use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\Event\Admin\ActivateAccountEvent as AdminActivateAccountEvent;
use Proximum\Vimeet\Application\Event\Admin\ResetPasswordEvent as AdminResetPasswordEvent;
use Proximum\Vimeet\Application\Event\Event\PreRegisterEvent;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Order\OrderConfirmEvent;
use Proximum\Vimeet\Application\Event\Sheet\SheetAddParticipantEvent;
use Proximum\Vimeet\Application\Event\Sheet\SheetChangedTypeEvent;
use Proximum\Vimeet\Application\Event\Sheet\SheetValidatedEvent;
use Proximum\Vimeet\Application\Event\Transaction\TransactionConfirmEvent;
use Proximum\Vimeet\Application\Event\User\ActivateAccountEvent as UserActivateAccountEvent;
use Proximum\Vimeet\Application\Event\User\ChangeMailAddressEvent;
use Proximum\Vimeet\Application\Event\User\CompleteProfileEvent as UserCompleteProfileEvent;
use Proximum\Vimeet\Application\Event\User\RegisteredEvent as UserRegisteredEvent;
use Proximum\Vimeet\Application\Event\User\ResetPasswordConfirmEvent;
use Proximum\Vimeet\Application\Event\User\ResetPasswordEvent as UserResetPasswordEvent;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service\EventSender;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Admin\ActivateAccountMail as AdminActivateAccountMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Admin\ResetPasswordMail as AdminResetPasswordMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Event\PreRegisteredMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Order\OrderConfirmMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Sheet\AddParticipantMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Sheet\SheetChangeTypeMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Sheet\SheetValidatedMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Transaction\TransactionConfirmMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\User\ActivateAccountMail as UserActivateAccountMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\User\ChangeNewMailAddressMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\User\ChangeOldMailAddressMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\User\CompleteProfileMail as UserCompleteProfileMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\User\RegisterAccountMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\User\ResetPasswordConfirmMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\User\ResetPasswordMail as UserResetPasswordMail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MailEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var MailerInterface
     */
    private $mailer;

    /**
     * @var EventSender
     */
    private $sender;

    /**
     * @var ParticipantInfoGuesser
     */
    private $participantInfoGuesser;

    /**
     * @var ParticipantRepositoryInterface
     */
    private $participantRepository;

    /**
     * MailEventSubscriber constructor.
     *
     * @param MailerInterface                $mailer
     * @param EventSender                    $sender
     * @param ParticipantInfoGuesser         $participantInfoGuesser
     * @param ParticipantRepositoryInterface $participantRepository
     */
    public function __construct(
        MailerInterface $mailer,
        EventSender $sender,
        ParticipantInfoGuesser $participantInfoGuesser,
        ParticipantRepositoryInterface $participantRepository
    ) {
        $this->mailer                 = $mailer;
        $this->sender                 = $sender;
        $this->participantInfoGuesser = $participantInfoGuesser;
        $this->participantRepository  = $participantRepository;
    }

    /**
     * Send email when admin validate user event participation
     *
     * @param SheetValidatedEvent $event
     */
    public function onSheetValidated(SheetValidatedEvent $event)
    {
        $owner = $event->getSheet()->getOwner();

        $mail = new SheetValidatedMail(
            $event->getSheet(),
            $this->sender->generate(),
            $owner->getEmail(),
            $owner->getLocale()
        );

        $this->mailer->send($mail);
    }

    /**
     * @param TransactionConfirmEvent $event
     */
    public function onTransactionConfirmed(TransactionConfirmEvent $event)
    {
        $participant = $this->participantRepository->getParticipantForUserAndSheet(
            $event->getUser(),
            $event->getTransaction()->getSheet()
        );

        if ($participant !== null) {
            $firstname = $this->participantInfoGuesser->guessParticipantFirstName(
                $participant,
                $event->getUser()->getLocale()
            );

            $lastname = $this->participantInfoGuesser->guessParticipantLastName(
                $participant,
                $event->getUser()->getLocale()
            );
        }

        $mail = new TransactionConfirmMail(
            $event->getTransaction(),
            $event->getUser(),
            $this->sender->generate($event->getTransaction()->getSheet()->getEvent()),
            $event->getUser()->getEmail(),
            $event->getUser()->getLocale(),
            $participant,
            isset($firstname) && $firstname !== null ? $firstname : '',
            isset($lastname) && $lastname !== null ? $lastname : ''
        );

        $this->mailer->send($mail);
    }

    /**
     * @param ChangeMailAddressEvent $event
     */
    public function onChangeMailAddressEvent(ChangeMailAddressEvent $event)
    {
        $oldMail = new ChangeOldMailAddressMail(
            $event->getEvent(),
            $this->sender->generate($event->getEvent()),
            $event->getUser()->getEmail(),
            $event->getUser()->getLocale(),
            $event->getChangeMailToken()->getMail(),
            $event->getUser()
        );

        $newMail = new ChangeNewMailAddressMail(
            $event->getEvent(),
            $this->sender->generate($event->getEvent()),
            $event->getChangeMailToken()->getMail(),
            $event->getUser()->getLocale(),
            $event->getChangeMailToken()->getToken(),
            $event->getUser()
        );

        $this->mailer->send($oldMail);
        $this->mailer->send($newMail);
    }

    /**
     * Send mail when order his confirmed to the buyer
     *
     * @param OrderConfirmEvent $event
     */
    public function onOrderConfirmed(OrderConfirmEvent $event)
    {
        $mail = new OrderConfirmMail(
            $this->sender->generate($event->getEvent()),
            $event->getUser()->getEmail(),
            $event->getUser()->getLocale(),
            $event->getOrder(),
            $event->getUser()
        );

        $this->mailer->send($mail);
    }

    /**
     * @param SheetAddParticipantEvent $event
     */
    public function onSheetAddParticipant(SheetAddParticipantEvent $event)
    {
        $firstname = $this->participantInfoGuesser->guessParticipantFirstName(
            $event->getGuest(),
            $event->getUser()->getLocale()
        );

        $lastname = $this->participantInfoGuesser->guessParticipantLastName(
            $event->getGuest(),
            $event->getUser()->getLocale()
        );

        $mail = new AddParticipantMail(
            $event->getSheet()->getEvent(),
            $this->sender->generate($event->getSheet()->getEvent()),
            $event->getUser()->getEmail(),
            $event->getUser()->getLocale(),
            $event->getUser(),
            $event->getGuest(),
            $firstname !== null ? $firstname : '',
            $lastname !== null ? $lastname : ''
        );

        $this->mailer->send($mail);
    }

    /**
     * @param AdminActivateAccountEvent $event
     */
    public function onAdminActivateAccount(AdminActivateAccountEvent $event)
    {
        $mail = new AdminActivateAccountMail(
            $this->sender->generate(),
            $event->getAdmin()->getEmail(),
            $event->getLocale(),
            $event->getActivateAccountToken()->getToken()
        );

        $this->mailer->send($mail);
    }

    /**
     * @param AdminResetPasswordEvent $event
     */
    public function onAdminResetPassword(AdminResetPasswordEvent $event)
    {
        $mail = new AdminResetPasswordMail(
            $this->sender->generate(),
            $event->getAdmin()->getEmail(),
            $event->getLocale(),
            $event->getForgottenPasswordToken()->getToken()
        );

        $this->mailer->send($mail);
    }

    /**
     * @param UserActivateAccountEvent $event
     */
    public function onUserActivateAccount(UserActivateAccountEvent $event)
    {
        $mail = new UserActivateAccountMail(
            $event->getEvent(),
            $this->sender->generate($event->getEvent()),
            $event->getUser()->getEmail(),
            $event->getUser()->getLocale(),
            $event->getActivateAccountToken()->getToken(),
            $event->getSender(),
            $event->getUser()
        );

        $this->mailer->send($mail);
    }

    /**
     * @param UserResetPasswordEvent $event
     */
    public function onUserResetPassword(UserResetPasswordEvent $event)
    {
        $mail = new UserResetPasswordMail(
            $event->getEvent(),
            $this->sender->generate($event->getEvent()),
            $event->getUser()->getEmail(),
            $event->getLocale(),
            $event->getForgottenPasswordToken()->getToken(),
            $event->getUser()
        );

        $this->mailer->send($mail);
    }

    /**
     * Send mail when user finished resetting his password
     *
     * @param ResetPasswordConfirmEvent $event
     */
    public function onUserResetPasswordConfirm(ResetPasswordConfirmEvent $event)
    {
        $mail = new ResetPasswordConfirmMail(
            $event->getEvent(),
            $this->sender->generate($event->getEvent()),
            $event->getUser()->getEmail(),
            $event->getLocale(),
            $event->getUser()
        );

        $this->mailer->send($mail);
    }

    /**
     * @param UserCompleteProfileEvent $event
     */
    public function onUserCompleteProfile(UserCompleteProfileEvent $event)
    {
        $firstname = $this->participantInfoGuesser->guessParticipantFirstName(
            $event->getParticipant(),
            $event->getLocale()
        );

        $lastname = $this->participantInfoGuesser->guessParticipantLastName(
            $event->getParticipant(),
            $event->getLocale()
        );

        $mail = new UserCompleteProfileMail(
            $event->getParticipant(),
            $this->sender->generate($event->getEvent()),
            $event->getUser()->getEmail(),
            $event->getLocale(),
            $firstname !== null ? $firstname : '',
            $lastname !== null ? $lastname : ''
        );

        $this->mailer->send($mail);
    }

    /**
     * Send email when user finish last step of event registration funnel
     *
     * @param PreRegisterEvent $event
     */
    public function onUserPreRegistered(PreRegisterEvent $event)
    {
        $mail = new PreRegisteredMail(
            $event->getParticipant(),
            $this->sender->generate($event->getParticipant()->getSheet()->getEvent()),
            $event->getUser()->getEmail(),
            $event->getLocale(),
            $event->getUser()
        );

        $this->mailer->send($mail);
    }

    /**
     * @param UserRegisteredEvent $event
     */
    public function onUserRegistered(UserRegisteredEvent $event)
    {
        $mail = new RegisterAccountMail(
            $event->getEvent(),
            $this->sender->generate($event->getEvent()),
            $event->getUser()->getEmail(),
            $event->getLocale(),
            $event->getUser()
        );

        $this->mailer->send($mail);
    }

    /**
     * @param SheetChangedTypeEvent $event
     */
    public function onSheetChangeType(SheetChangedTypeEvent $event)
    {
        $participant = $this->participantRepository->getParticipantForUserAndSheet(
            $event->getSheet()->getOwner(),
            $event->getSheet()
        );

        if ($participant !== null) {
            $firstname = $this->participantInfoGuesser->guessParticipantFirstName(
                $participant,
                $event->getSheet()->getOwner()->getLocale()
            );

            $lastname = $this->participantInfoGuesser->guessParticipantLastName(
                $participant,
                $event->getSheet()->getOwner()->getLocale()
            );
        }

        $mail = new SheetChangeTypeMail(
            $event->getSheet()->getEvent(),
            $this->sender->generate($event->getSheet()->getEvent()),
            $event->getSheet()->getOwner()->getEmail(),
            $event->getLocale(),
            $event->getSheet()->getOwner(),
            $event->getFromTypeTitle(),
            $event->getSheet()->getType()->getTitle($event->getLocale()),
            (isset($firstname) && $firstname !== null) ? $firstname : '',
            (isset($lastname) && $lastname !== null) ? $lastname : ''
        );

        $this->mailer->send($mail);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::ADMIN_ACCOUNT_ACTIVATED            => 'onAdminActivateAccount',
            Events::ADMIN_PASSWORD_RESET               => 'onAdminResetPassword',
            Events::SHEET_VALIDATED                    => 'onSheetValidated',
            Events::SHEET_ADD_PARTICIPANT_CONFIRMATION => 'onSheetAddParticipant',
            Events::USER_MAIL_CHANGED                  => 'onChangeMailAddressEvent',
            Events::USER_ACCOUNT_ACTIVATED             => 'onUserActivateAccount',
            Events::USER_PASSWORD_RESET                => 'onUserResetPassword',
            Events::USER_PROFILE_COMPLETED             => 'onUserCompleteProfile',
            Events::USER_REGISTERED                    => 'onUserRegistered',
            Events::USER_RESET_PASSWORD_CONFIRMED      => 'onUserResetPasswordConfirm',
            Events::EVENT_PRE_REGISTERED               => 'onUserPreRegistered',
            Events::ORDER_CONFIRMED                    => 'onOrderConfirmed',
            Events::TRANSACTION_CONFIRMED              => 'onTransactionConfirmed',
            Events::SHEET_CHANGED_TYPE                 => 'onSheetChangeType',
        ];
    }
}
