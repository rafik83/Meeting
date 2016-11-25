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
use Proximum\Vimeet\Application\Query\Mail\ParticipantMailViewQuery;
use Proximum\Vimeet\Application\Query\Mail\ParticipantMailViewQueryHandler;
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
     * @var ParticipantMailViewQueryHandler
     */
    private $participantMailViewQueryHandler;

    /**
     * MailEventSubscriber constructor.
     *
     * @param MailerInterface                 $mailer
     * @param EventSender                     $sender
     * @param ParticipantMailViewQueryHandler $participantMailViewQueryHandler
     */
    public function __construct(
        MailerInterface $mailer,
        EventSender $sender,
        ParticipantMailViewQueryHandler $participantMailViewQueryHandler
    ) {
        $this->mailer                          = $mailer;
        $this->sender                          = $sender;
        $this->participantMailViewQueryHandler = $participantMailViewQueryHandler;
    }

    /**
     * Send email when admin validate user event participation
     *
     * @param SheetValidatedEvent $event
     */
    public function onSheetValidated(SheetValidatedEvent $event)
    {
        $owner = $event->getSheet()->getOwner();

        $participantMailView = $this->participantMailViewQueryHandler->handle(
            new ParticipantMailViewQuery($event->getSheet(), $owner)
        );

        $mail = new SheetValidatedMail(
            $event->getSheet(),
            $this->sender->generate($event->getSheet()->getEvent()),
            $owner->getEmail(),
            $owner->getLocale(),
            $participantMailView
        );

        $this->mailer->send($mail);
    }

    /**
     * @param TransactionConfirmEvent $event
     */
    public function onTransactionConfirmed(TransactionConfirmEvent $event)
    {
        $participantMailView = $this->participantMailViewQueryHandler->handle(
            new ParticipantMailViewQuery($event->getTransaction()->getSheet(), $event->getUser())
        );

        $mail = new TransactionConfirmMail(
            $event->getTransaction(),
            $event->getUser(),
            $this->sender->generate($event->getTransaction()->getSheet()->getEvent()),
            $event->getUser()->getEmail(),
            $event->getUser()->getLocale(),
            $participantMailView
        );

        $this->mailer->send($mail);
    }

    /**
     * @param ChangeMailAddressEvent $event
     */
    public function onChangeMailAddressEvent(ChangeMailAddressEvent $event)
    {
        $participantMailView = $this->participantMailViewQueryHandler->handle(
            new ParticipantMailViewQuery(null, $event->getUser())
        );

        $oldMail = new ChangeOldMailAddressMail(
            $event->getEvent(),
            $this->sender->generate($event->getEvent()),
            $event->getUser()->getEmail(),
            $event->getUser()->getLocale(),
            $event->getChangeMailToken()->getMail(),
            $participantMailView
        );

        $newMail = new ChangeNewMailAddressMail(
            $event->getEvent(),
            $this->sender->generate($event->getEvent()),
            $event->getChangeMailToken()->getMail(),
            $event->getUser()->getLocale(),
            $event->getChangeMailToken()->getToken(),
            $participantMailView
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
        $participantMailView = $this->participantMailViewQueryHandler->handle(
            new ParticipantMailViewQuery($event->getOrder()->getSheet(), $event->getUser())
        );

        $mail = new OrderConfirmMail(
            $this->sender->generate($event->getEvent()),
            $event->getUser()->getEmail(),
            $event->getUser()->getLocale(),
            $event->getOrder(),
            $participantMailView
        );

        $this->mailer->send($mail);
    }

    /**
     * @param SheetAddParticipantEvent $event
     */
    public function onSheetAddParticipant(SheetAddParticipantEvent $event)
    {
        $participantMailView = $this->participantMailViewQueryHandler->handle(
            new ParticipantMailViewQuery($event->getSheet(), $event->getUser())
        );

        $mail = new AddParticipantMail(
            $event->getSheet()->getEvent(),
            $this->sender->generate($event->getSheet()->getEvent()),
            $event->getUser()->getEmail(),
            $event->getUser()->getLocale(),
            $event->getGuest()->getUser(),
            $participantMailView
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
        $participantMailView = $this->participantMailViewQueryHandler->handle(
            new ParticipantMailViewQuery(null, $event->getUser())
        );

        $mail = new UserActivateAccountMail(
            $event->getEvent(),
            $this->sender->generate($event->getEvent()),
            $event->getUser()->getEmail(),
            $event->getUser()->getLocale(),
            $event->getActivateAccountToken()->getToken(),
            $participantMailView
        );

        $this->mailer->send($mail);
    }

    /**
     * @param UserResetPasswordEvent $event
     */
    public function onUserResetPassword(UserResetPasswordEvent $event)
    {
        $participantMailView = $this->participantMailViewQueryHandler->handle(
            new ParticipantMailViewQuery(null, $event->getUser())
        );

        $mail = new UserResetPasswordMail(
            $event->getEvent(),
            $this->sender->generate($event->getEvent()),
            $event->getUser()->getEmail(),
            $event->getLocale(),
            $event->getForgottenPasswordToken()->getToken(),
            $participantMailView
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
        $participantMailView = $this->participantMailViewQueryHandler->handle(
            new ParticipantMailViewQuery(null, $event->getUser())
        );

        $mail = new ResetPasswordConfirmMail(
            $event->getEvent(),
            $this->sender->generate($event->getEvent()),
            $event->getUser()->getEmail(),
            $event->getLocale(),
            $participantMailView
        );

        $this->mailer->send($mail);
    }

    /**
     * @param UserCompleteProfileEvent $event
     */
    public function onUserCompleteProfile(UserCompleteProfileEvent $event)
    {
        $participantMailView = $this->participantMailViewQueryHandler->handle(
            new ParticipantMailViewQuery($event->getParticipant()->getSheet(), $event->getUser())
        );

        $mail = new UserCompleteProfileMail(
            $event->getParticipant(),
            $this->sender->generate($event->getEvent()),
            $event->getUser()->getEmail(),
            $event->getLocale(),
            $participantMailView
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
        $participantMailView = $this->participantMailViewQueryHandler->handle(
            new ParticipantMailViewQuery($event->getSheet(), $event->getUser())
        );

        $mail = new PreRegisteredMail(
            $event->getParticipant(),
            $this->sender->generate($event->getParticipant()->getSheet()->getEvent()),
            $event->getUser()->getEmail(),
            $event->getLocale(),
            $participantMailView
        );

        $this->mailer->send($mail);
    }

    /**
     * @param UserRegisteredEvent $event
     */
    public function onUserRegistered(UserRegisteredEvent $event)
    {
        $participantMailView = $this->participantMailViewQueryHandler->handle(
            new ParticipantMailViewQuery(null, $event->getUser())
        );

        $mail = new RegisterAccountMail(
            $event->getEvent(),
            $this->sender->generate($event->getEvent()),
            $event->getUser()->getEmail(),
            $event->getLocale(),
            $participantMailView
        );

        $this->mailer->send($mail);
    }

    /**
     * @param SheetChangedTypeEvent $event
     */
    public function onSheetChangeType(SheetChangedTypeEvent $event)
    {
        $participantMailView = $this->participantMailViewQueryHandler->handle(
            new ParticipantMailViewQuery($event->getSheet(), $event->getSheet()->getOwner())
        );

        $mail = new SheetChangeTypeMail(
            $event->getSheet()->getEvent(),
            $this->sender->generate($event->getSheet()->getEvent()),
            $event->getSheet()->getOwner()->getEmail(),
            $event->getLocale(),
            $event->getSheet()->getOwner(),
            $event->getFromTypeTitle(),
            $event->getSheet()->getType()->getTitle($event->getLocale()),
            $participantMailView
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
