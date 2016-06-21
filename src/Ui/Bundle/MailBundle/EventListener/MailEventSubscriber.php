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
use Proximum\Vimeet\Application\Event\User\ActivateAccountEvent as UserActivateAccountEvent;
use Proximum\Vimeet\Application\Event\User\ResetPasswordEvent as UserResetPasswordEvent;
use Proximum\Vimeet\Application\Event\User\CompleteProfileEvent as UserCompleteProfileEvent;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetValidatedEvent;
use Proximum\Vimeet\Application\Event\User\ChangeMailAddressEvent;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Admin\ActivateAccountMail as AdminActivateAccountMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Admin\ResetPasswordMail as AdminResetPasswordMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\User\ActivateAccountMail as UserActivateAccountMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\User\ResetPasswordMail as UserResetPasswordMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\ChangeNewMailAddressMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\ChangeOldMailAddressMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\SheetValidatedMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\User\CompleteProfileMail as UserCompleteProfileMail;
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
     * @param SheetValidatedEvent $event
     */
    public function onSheetValidated(SheetValidatedEvent $event)
    {
        $owner = $event->getSheet()->getOwner();

        $mail  = new SheetValidatedMail(
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
            $event->getActivateAccountToken()->getToken()
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
            $event->getEventView()->title,
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
            $event->getEventView()->title,
            $event->getParticipant()->getId()
        );

        $this->mailer->send($mail);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::SHEET_VALIDATED   => 'onSheetValidated',
            Events::USER_MAIL_CHANGED => 'onChangeMailAddressEvent',
            'admin_activate_account'  => 'onAdminActivateAccount',
            'admin_reset_password'    => 'onAdminResetPassword',
            'user_activate_account'   => 'onUserActivateAccount',
            'user_reset_password'     => 'onUserResetPassword',
            'user_complete_profile'   => 'onUserCompleteProfile',
        ];
    }
}
