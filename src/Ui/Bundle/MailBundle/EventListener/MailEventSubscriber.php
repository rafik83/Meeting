<?php

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\EventListener;

use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\Components\Mail\AbstractMail;
use Proximum\Vimeet\Application\Components\Transactional\Mail\PrepareHandler;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareActivateAccountMailView;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareChangeNewMailAccountView;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareChangeOldMailAccountView;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareOrderConfirmedMailView;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareParticipantAddedMailView;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PreparePreRegisterMailView;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareSheetChangeTypeMailView;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareTransactionConfirmMailView;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareUserCompleteProfileMailView;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareUserRegisteredMailView;
use Proximum\Vimeet\Application\Event\Admin\ActivateAccountEvent as AdminActivateAccountEvent;
use Proximum\Vimeet\Application\Event\Admin\ResetPasswordEvent as AdminResetPasswordEvent;
use Proximum\Vimeet\Application\Event\Event\PreRegisterEvent;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Happening\Webinar\ZipRecordArchiveNotPreparedEvent;
use Proximum\Vimeet\Application\Event\Happening\Webinar\ZipRecordArchivePreparedEvent;
use Proximum\Vimeet\Application\Event\Meeting\MeetingsDeletedAllEvent;
use Proximum\Vimeet\Application\Event\Order\OrderConfirmEvent;
use Proximum\Vimeet\Application\Event\Sheet\AbstractGroupEvent;
use Proximum\Vimeet\Application\Event\Sheet\SheetAddParticipantEvent;
use Proximum\Vimeet\Application\Event\Sheet\SheetChangedTypeEvent;
use Proximum\Vimeet\Application\Event\Sheet\SheetGroupCreatedEvent;
use Proximum\Vimeet\Application\Event\Sheet\SheetGroupUpdatedEvent;
use Proximum\Vimeet\Application\Event\Transaction\TransactionConfirmedEvent;
use Proximum\Vimeet\Application\Event\User\ActivateAccountEvent as UserActivateAccountEvent;
use Proximum\Vimeet\Application\Event\User\ActivateAccountFromLoginEvent as UserActivateAccountFromLoginEvent;
use Proximum\Vimeet\Application\Event\User\AdminTemporarilyDisabledEvent;
use Proximum\Vimeet\Application\Event\User\ChangeMailAddressEvent;
use Proximum\Vimeet\Application\Event\User\CompleteProfileEvent as UserCompleteProfileEvent;
use Proximum\Vimeet\Application\Event\User\RegisteredEvent as UserRegisteredEvent;
use Proximum\Vimeet\Application\Event\User\ResetPasswordConfirmEvent;
use Proximum\Vimeet\Application\Event\User\ResetPasswordEvent as UserResetPasswordEvent;
use Proximum\Vimeet\Application\Event\User\UserTemporarilyDisabledEvent;
use Proximum\Vimeet\Application\Query\Mail\ParticipantMailViewQuery;
use Proximum\Vimeet\Application\Query\Mail\ParticipantMailViewQueryHandler;
use Proximum\Vimeet\Application\View\Participant\ParticipantInfoView;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service\EventSender;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Admin\AccountTemporarilyDisabledMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Admin\ActivateAccountMail as AdminActivateAccountMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Admin\ResetPasswordMail as AdminResetPasswordMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Happening\Webinar\AdminZipRecordArchiveNotPreparedMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Happening\Webinar\AdminZipRecordArchivePreparedMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Happening\Webinar\ZipRecordArchiveAvailableForSpeakerMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Meeting\AdminMeetingsDeletedAllMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Sheet\SheetGroupCreatedMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\User\ResetPasswordConfirmMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\User\ResetPasswordMail as UserResetPasswordMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\User\UserAccountTemporarilyDisabledMail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MailEventSubscriber implements EventSubscriberInterface
{
    /** @var MailerInterface */
    private $mailer;

    /** @var EventSender */
    private $sender;

    /** @var ParticipantMailViewQueryHandler */
    private $participantMailViewQueryHandler;

    /** @var PrepareHandler */
    private $prepareHandler;

    public function __construct(
        MailerInterface $mailer,
        EventSender $sender,
        ParticipantMailViewQueryHandler $participantMailViewQueryHandler,
        PrepareHandler $prepareHandler
    ) {
        $this->mailer = $mailer;
        $this->sender = $sender;
        $this->participantMailViewQueryHandler = $participantMailViewQueryHandler;
        $this->prepareHandler = $prepareHandler;
    }

    /**
     * @param TransactionConfirmedEvent $event
     */
    public function onTransactionConfirmed(TransactionConfirmedEvent $event): void
    {
        $mail = $this->prepareHandler->handle(new PrepareTransactionConfirmMailView(
            $event->getTransaction()->getSheet()->getEvent(),
            $event->getUser(),
            $event->getUser()->getLocale(),
            $event->getTransaction()->getSheet(),
            $event->getTransaction()
        ));

        if (!$mail instanceof AbstractMail) {
            return;
        }

        $this->mailer->send($mail);
    }

    /**
     * @param ChangeMailAddressEvent $event
     */
    public function onChangeMailAddressEvent(ChangeMailAddressEvent $event)
    {

        $newMail = $this->prepareHandler->handle(
            new PrepareChangeNewMailAccountView(
                $event->getEvent(),
                $event->getUser(),
                $event->getUser()->getLocale(),
                $event->getChangeMailToken()
            )
        );
        if ($newMail instanceof AbstractMail) {
            $this->mailer->send($newMail);
        }

        $oldMail = $this->prepareHandler->handle(
            new PrepareChangeOldMailAccountView(
                $event->getEvent(),
                $event->getUser(),
                $event->getUser()->getLocale(),
                $event->getChangeMailToken()
            )
        );
        if (!$oldMail instanceof AbstractMail) {
            return;
        }

        $this->mailer->send($oldMail);

    }

    /**
     * Send mail when order his confirmed to the buyer
     *
     * @param OrderConfirmEvent $event
     */
    public function onOrderConfirmed(OrderConfirmEvent $event): void
    {
        $mail = $this->prepareHandler->handle(new PrepareOrderConfirmedMailView(
            $event->getEvent(),
            $event->getUser(),
            $event->getUser()->getLocale(),
            $event->getOrder()->getSheet(),
            $event->getOrder()
        ));

        if (!$mail instanceof AbstractMail) {
            return;
        }

        $this->mailer->send($mail);
    }

    /**
     * @param SheetAddParticipantEvent $event
     */
    public function onSheetAddParticipant(SheetAddParticipantEvent $event): void
    {
        $mail = $this->prepareHandler->handle(new PrepareParticipantAddedMailView(
            $event->getSheet()->getEvent(),
            $event->getUser(),
            $event->getUser()->getLocale(),
            $event->getSheet(),
            $event->getGuest()
        ));

        if (!$mail instanceof AbstractMail) {
            return;
        }

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

    public function onAdminAccountTemporarilyDisabled(AdminTemporarilyDisabledEvent $event): void
    {
        $this->mailer->send(
            new AccountTemporarilyDisabledMail(
                $this->sender->generate(),
                $event->getAdmin()->getEmail(),
                $event->getLocale(),
                $event->getAdmin()
            )
        );
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
    public function onUserActivateAccount(UserActivateAccountEvent $event): void
    {
        $mail = $this->prepareHandler->handle(new PrepareActivateAccountMailView(
            $event->getEvent(),
            $event->getUser(),
            $event->getUser()->getLocale(),
            $event->getActivateAccountToken()
        ));

        if (!$mail instanceof AbstractMail) {
            return;
        }

        $this->mailer->send($mail);
    }

    /**
     * @param UserActivateAccountFromLoginEvent $event
     */
    public function onUserActivateAccountFromLogin(UserActivateAccountFromLoginEvent $event): void
    {
        $mail = $this->prepareHandler->handle(new PrepareActivateAccountMailView(
            $event->getEvent(),
            $event->getUser(),
            $event->getUser()->getLocale(),
            $event->getActivateAccountToken()
        ));

        if (!$mail instanceof AbstractMail) {
            return;
        }

        $this->mailer->send($mail);
    }

    /**
     * @param UserResetPasswordEvent $event
     */
    public function onUserResetPassword(UserResetPasswordEvent $event)
    {
        if ($event->isRequestedByAdmin()) {
            return;
        }

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
    public function onUserCompleteProfile(UserCompleteProfileEvent $event): void
    {
        $mail = $this->prepareHandler->handle(new PrepareUserCompleteProfileMailView(
            $event->getEvent(),
            $event->getUser(),
            $event->getLocale(),
            $event->getParticipant()->getSheet(),
            $event->getParticipant()
        ));

        if (!$mail instanceof AbstractMail) {
            return;
        }

        $this->mailer->send($mail);
    }

    /**
     * Send email when user finish last step of event registration funnel
     *
     * @param PreRegisterEvent $event
     */
    public function onUserPreRegistered(PreRegisterEvent $event): void
    {
        $mail = $this->prepareHandler->handle(new PreparePreRegisterMailView(
            $event->getEvent(),
            $event->getParticipant()->getUser(),
            $event->getLocale(),
            $event->getSheet(),
            $event->getParticipant()
        ));

        if (!$mail instanceof AbstractMail) {
            return;
        }

        $this->mailer->send($mail);
    }

    /**
     * @param UserRegisteredEvent $event
     */
    public function onUserRegistered(UserRegisteredEvent $event): void
    {
        $mail = $this->prepareHandler->handle(new PrepareUserRegisteredMailView(
            $event->getEvent(),
            $event->getUser(),
            $event->getLocale()
        ));

        if (!$mail instanceof AbstractMail) {
            return;
        }

        $this->mailer->send($mail);
    }

    /**
     * @param SheetChangedTypeEvent $event
     */
    public function onSheetChangeType(SheetChangedTypeEvent $event): void
    {
        $mail = $this->prepareHandler->handle(new PrepareSheetChangeTypeMailView(
            $event->getSheet()->getEvent(),
            $event->getSheet(),
            $event->getSheet()->getOwner(),
            $event->getLocale(),
            $event->getFromTypeTitle()
        ));

        if (!$mail instanceof AbstractMail) {
            return;
        }

        $this->mailer->send($mail);
    }

    public function onSheetGroupCreated(SheetGroupCreatedEvent $event): void
    {
        $mail = $this->getSheetGroupMail($event);

        $this->mailer->send($mail);
    }

    /**
     * @param SheetGroupUpdatedEvent $event
     */
    public function onSheetGroupUpdated(SheetGroupUpdatedEvent $event)
    {
        if (!$event->isManagerChanged()) {
            return;
        }

        $mail = $this->getSheetGroupMail($event);

        $this->mailer->send($mail);
    }

    public function onAdminMeetingsDeletedAll(MeetingsDeletedAllEvent $meetingsDeletedAllEvent)
    {
        $event = $meetingsDeletedAllEvent->getEvent();

        if (null === $event->getEmailTeam()) {
            return;
        }

        $this->mailer->send(
            new AdminMeetingsDeletedAllMail(
                $this->sender->generate($event),
                $event->getEmailTeam(),
                $event->getFallback(),
                $meetingsDeletedAllEvent->getAdmin(),
                $event
            )
        );
    }

    public function onUserAccountTemporarilyDisabled(UserTemporarilyDisabledEvent $userTemporarilyDisabledEvent): void
    {
        $event = $userTemporarilyDisabledEvent->getEvent();
        $user = $userTemporarilyDisabledEvent->getUser();

        $this->mailer->send(
            new UserAccountTemporarilyDisabledMail(
                $event,
                $this->sender->generate($event),
                $user->getEmail(),
                $event->getAvailableLocale($user->getLocale()),
                new ParticipantInfoView($user->getFirstName(), $user->getLastName())
            )
        );
    }

    public function onHappeningZipRecordArchivePrepared(ZipRecordArchivePreparedEvent $event): void
    {
        $domainEvent = $event->getEvent();
        $happening = $event->getHappening();

        if ($event->hasAdmin()) {
            $this->mailer->send(
                new AdminZipRecordArchivePreparedMail(
                    $happening,
                    $domainEvent,
                    $this->sender->generate($domainEvent),
                    $event->getAdmin()->getEmail(),
                    $event->getLocale()
                )
            );
        } else {
            /*
            foreach ($happening->getSpeakers() as $speaker) {
                $speakerUser = $speaker->getUser();

                if ($speakerUser instanceof User) {
                    $this->mailer->send(
                        new ZipRecordArchiveAvailableForSpeakerMail(
                            $happening,
                            $domainEvent,
                            $this->sender->generate($domainEvent),
                            $speakerUser->getEmail(),
                            $domainEvent->getAvailableLocale($speakerUser->getLocale())
                        )
                    );
                }
            }
            */
        }
    }

    public function onHappeningZipRecordArchiveNotPrepared(ZipRecordArchiveNotPreparedEvent $event): void
    {
        $this->mailer->send(
            new AdminZipRecordArchiveNotPreparedMail(
                $event->getHappening(),
                $event->getEvent(),
                $this->sender->generate($event->getEvent()),
                $event->getAdmin()->getEmail(),
                $event->getLocale()
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            Events::ADMIN_ACCOUNT_ACTIVATED            => 'onAdminActivateAccount',
            Events::ADMIN_ACCOUNT_TEMPORARILY_DISABLED => 'onAdminAccountTemporarilyDisabled',
            Events::ADMIN_PASSWORD_RESET               => 'onAdminResetPassword',
            Events::ADMIN_MEETINGS_DELETED_ALL         => 'onAdminMeetingsDeletedAll',
            Events::SHEET_ADD_PARTICIPANT_CONFIRMATION => 'onSheetAddParticipant',
            Events::USER_MAIL_CHANGED                  => 'onChangeMailAddressEvent',
            Events::USER_ACCOUNT_ACTIVATED             => 'onUserActivateAccount',
            Events::USER_ACCOUNT_ACTIVATED_FROM_LOGIN  => 'onUserActivateAccountFromLogin',
            Events::USER_PASSWORD_RESET                => 'onUserResetPassword',
            Events::USER_PROFILE_COMPLETED             => 'onUserCompleteProfile',
            Events::USER_REGISTERED                    => 'onUserRegistered',
            Events::USER_RESET_PASSWORD_CONFIRMED      => 'onUserResetPasswordConfirm',
            Events::USER_ACCOUNT_TEMPORARILY_DISABLED  => 'onUserAccountTemporarilyDisabled',
            Events::EVENT_PRE_REGISTERED               => 'onUserPreRegistered',
            Events::ORDER_CONFIRMED                    => 'onOrderConfirmed',
            Events::TRANSACTION_CONFIRMED              => 'onTransactionConfirmed',
            Events::SHEET_CHANGED_TYPE                 => 'onSheetChangeType',
            Events::SHEET_GROUP_CREATED                => 'onSheetGroupCreated',
            Events::SHEET_GROUP_UPDATED                => 'onSheetGroupUpdated',
            Events::HAPPENING_ZIP_RECORD_ARCHIVE_PREPARED => 'onHappeningZipRecordArchivePrepared',
            Events::HAPPENING_ZIP_RECORD_ARCHIVE_NOT_PREPARED => 'onHappeningZipRecordArchiveNotPrepared',
        ];
    }

    /**
     * @param AbstractGroupEvent $event
     *
     * @return SheetGroupCreatedMail
     */
    private function getSheetGroupMail(AbstractGroupEvent $event)
    {
        $participantMailView = $this->participantMailViewQueryHandler->handle(
            new ParticipantMailViewQuery(null, $event->getGroup()->getManager())
        );

        $mail = new SheetGroupCreatedMail(
            $this->sender->generate($event->getGroup()->getEvent()),
            $event->getGroup()->getManager()->getEmail(),
            $event->getGroup()->getEvent()->getAvailableLocale(
                $event->getGroup()->getManager()->getLocale()
            ),
            $event->getGroup()->getEvent(),
            $participantMailView,
            $event->getGroup()
        );

        return $mail;
    }
}
