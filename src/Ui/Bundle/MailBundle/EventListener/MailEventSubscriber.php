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
use Proximum\Vimeet\Application\Event\Sheet\SheetAddParticipantEvent;
use Proximum\Vimeet\Application\Event\Sheet\SheetInvitationCloseToExpiration;
use Proximum\Vimeet\Application\Event\Sheet\SheetInvitationExpire;
use Proximum\Vimeet\Application\Event\Sheet\SheetValidatedEvent;
use Proximum\Vimeet\Application\Event\User\ActivateAccountEvent as UserActivateAccountEvent;
use Proximum\Vimeet\Application\Event\User\ChangeMailAddressEvent;
use Proximum\Vimeet\Application\Event\User\CompleteProfileEvent as UserCompleteProfileEvent;
use Proximum\Vimeet\Application\Event\User\RegisteredEvent as UserRegisteredEvent;
use Proximum\Vimeet\Application\Event\User\ResetPasswordEvent as UserResetPasswordEvent;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Admin\ActivateAccountMail as AdminActivateAccountMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Admin\ResetPasswordMail as AdminResetPasswordMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\ChangeNewMailAddressMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\ChangeOldMailAddressMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Event\PreRegisteredMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Sheet\AddParticipantMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Sheet\ExpiredInvitationMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Sheet\InvitationCloseToExpirationMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Sheet\SheetValidatedMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\User\ActivateAccountMail as UserActivateAccountMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\User\CompleteProfileMail as UserCompleteProfileMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\User\RegisterAccountMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\User\ResetPasswordMail as UserResetPasswordMail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MailEventSubscriber implements EventSubscriberInterface
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
     * MailEventSubscriber constructor.
     *
     * @param MailerInterface $mailer
     * @param string          $sender
     */
    public function __construct(MailerInterface $mailer, $sender)
    {
        $this->mailer = $mailer;
        $this->sender = $sender;
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
            $this->sender,
            $owner->getEmail(),
            'MailBundle:Mail:Sheet/sheetValidated.html.twig',
            'sheet_validated',
            $owner->getLocale()
        );

        $this->mailer->send($mail);
    }

    /**
     * @param ChangeMailAddressEvent $event
     */
    public function onChangeMailAddressEvent(ChangeMailAddressEvent $event)
    {
        $oldMail = new ChangeOldMailAddressMail(
            $this->sender,
            $event->getUser()->getEmail(),
            'MailBundle:Mail:ChangeMail/oldMail.html.twig',
            'change_mail_old',
            $event->getUser()->getLocale(),
            $event->getChangeMailToken()->getMail()
        );

        $newMail = new ChangeNewMailAddressMail(
            $this->sender,
            $event->getChangeMailToken()->getMail(),
            'MailBundle:Mail:ChangeMail/newMail.html.twig',
            'change_mail_new',
            $event->getUser()->getLocale(),
            $event->getChangeMailToken()->getToken()
        );

        $this->mailer->send($oldMail);
        $this->mailer->send($newMail);
    }

    /**
     * Send mail to the guest for notice him of an invitation close to expiration
     *
     * @param SheetInvitationCloseToExpiration $event
     */
    public function onInvitationCloseToExpiration(SheetInvitationCloseToExpiration $event)
    {
        $mail = new InvitationCloseToExpirationMail(
            $this->sender,
            $event->getGuest()->getEmail(),
            'MailBundle:Mail:Sheet/Invitation/closeToExpiration.html.twig',
            'sheet_invitation_close_to_expiration',
            $event->getUser()->getLocale(),
            $event->getSheet()->getEvent(),
            $event->getGuest()
        );

        $this->mailer->send($mail);
    }

    /**
     * Send mail to the host for notice him of a guest expired invitation
     *
     * @param SheetInvitationExpire $event
     */
    public function onInvitationExpire(SheetInvitationExpire $event)
    {
        $mail = new ExpiredInvitationMail(
            $this->sender,
            $event->getSheet()->getOwner(),
            'MailBundle:Mail:Sheet/Invitation/expiredInvitation.html.twig',
            'sheet_invitation_expired',
            $event->getSheet()->getOwner()->getLocale(),
            $event->getSheet()->getEvent(),
            $event->getGuest()
        );

        $this->mailer->send($mail);
    }

    /**
     * @param SheetAddParticipantEvent $event
     */
    public function onSheetAddParticipant(SheetAddParticipantEvent $event)
    {
        $mail = new AddParticipantMail(
            $this->sender,
            $event->getGuest()->getEmail(),
            'MailBundle:Mail:Sheet/Invitation/addParticipantConfirmation.html.twig',
            'sheet_add_participant_confirmation',
            $event->getUser()->getLocale(),
            $event->getSheet()->getEvent(),
            $event->getUser()
        );

        $this->mailer->send($mail);
    }

    /**
     * @param AdminActivateAccountEvent $event
     */
    public function onAdminActivateAccount(AdminActivateAccountEvent $event)
    {
        $mail = new AdminActivateAccountMail(
            $this->sender,
            $event->getAdmin()->getEmail(),
            'MailBundle:Mail:Admin/activateAccount.html.twig',
            'admin_activate_account',
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
            $this->sender,
            $event->getAdmin()->getEmail(),
            'MailBundle:Mail:Admin/resetPassword.html.twig',
            'admin_forgot_password',
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
            $this->sender,
            $event->getUser()->getEmail(),
            'MailBundle:Mail:User/activateAccount.html.twig',
            'user_activate_account',
            $event->getUser()->getLocale(),
            $event->getEvent(),
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
            $this->sender,
            $event->getUser()->getEmail(),
            'MailBundle:Mail:User/resetPassword.html.twig',
            'user_forgot_password',
            $event->getLocale(),
            $event->getEvent()->getTitle(),
            $event->getForgottenPasswordToken()->getToken()
        );

        $this->mailer->send($mail);
    }

    /**
     * @param UserCompleteProfileEvent $event
     */
    public function onUserCompleteProfile(UserCompleteProfileEvent $event)
    {
        $mail = new UserCompleteProfileMail(
            $this->sender,
            $event->getUser()->getEmail(),
            'MailBundle:Mail:User/completeProfile.html.twig',
            'user_complete_profile',
            $event->getLocale(),
            $event->getEvent()->getTitle(),
            $event->getParticipant()->getId()
        );

        $this->mailer->send($mail);
    }

    /**
     * Send email when user finish step 3 of event registration funnel
     *
     * @param PreRegisterEvent $event
     */
    public function onUserPreRegistered(PreRegisterEvent $event)
    {
        $mail = new PreRegisteredMail(
            $this->sender,
            $event->getUser()->getEmail(),
            'MailBundle:Mail:Event/preregister.html.twig',
            'event_pre_registered',
            $event->getLocale(),
            $event->getEvent(),
            $event->getUser(),
            $event->getParticipant(),
            $event->getSheet(),
            $event->getParticipantData()
        );

        $this->mailer->send($mail);
    }

    /**
     * @param UserRegisteredEvent $event
     */
    public function onUserRegistered(UserRegisteredEvent $event)
    {
        $mail = new RegisterAccountMail(
            $this->sender,
            $event->getUser()->getEmail(),
            'MailBundle:Mail:User/register.html.twig',
            'user_registered',
            $event->getLocale(),
            $event->getEvent(),
            $event->getUser()
        );

        $this->mailer->send($mail);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::SHEET_VALIDATED                      => 'onSheetValidated',
            Events::SHEET_ADD_PARTICIPANT                => 'onSheetAddParticipant',
            Events::SHEET_INVITATION_CLOSE_TO_EXPIRATION => 'onInvitationCloseToExpiration',
            Events::SHEET_INVITATION_EXPIRE              => 'onInvitationExpire',
            Events::USER_MAIL_CHANGED                    => 'onChangeMailAddressEvent',
            'admin_activate_account'                     => 'onAdminActivateAccount',
            'admin_reset_password'                       => 'onAdminResetPassword',
            'user_activate_account'                      => 'onUserActivateAccount',
            'user_reset_password'                        => 'onUserResetPassword',
            'user_complete_profile'                      => 'onUserCompleteProfile',
            Events::USER_REGISTERED                      => 'onUserRegistered',
            Events::EVENT_PRE_REGISTERED                 => 'onUserPreRegistered',
        ];
    }
}
