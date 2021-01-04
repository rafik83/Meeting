<?php

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\EventListener;

use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetSubmittedEvent;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Sheet\SheetSubmittedMail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Notify follower or all organizers allowed to manage this sheet
 * when user submit its sheet
 */
class SubmitSheetMailEventSubscriber implements EventSubscriberInterface
{
    /** @var MailerInterface */
    private $mailer;

    /** @var string */
    private $sender;

    /** @var AdminRepositoryInterface */
    private $adminRepository;

    /** @var ParticipantInfoGuesser */
    private $participantInfoGuesser;

    /** @var \DateTimeInterface */
    private $datetime;

    /**
     * @param ParticipantInfoGuesser   $participantInfoGuesser
     * @param AdminRepositoryInterface $adminRepository
     * @param MailerInterface          $mailer
     * @param string                   $sender
     * @param \DateTimeInterface       $datetime
     */
    public function __construct(
        ParticipantInfoGuesser $participantInfoGuesser,
        AdminRepositoryInterface $adminRepository,
        MailerInterface $mailer,
        $sender,
        \DateTimeInterface $datetime
    ) {
        $this->participantInfoGuesser = $participantInfoGuesser;
        $this->adminRepository        = $adminRepository;
        $this->mailer                 = $mailer;
        $this->sender                 = $sender;
        $this->datetime               = $datetime;
    }

    /**
     * @param SheetSubmittedEvent $event
     */
    public function onSheetSubmittedValidation(SheetSubmittedEvent $event)
    {
        $admins   = [];
        $follower = $event->getSheet()->getFollower();
        $locale   = $event->getSheet()->getEvent()->getFallback();

        $sheetName = $event->getSheet()->getTitle();
        $participant = $event->getSheet()->getUserParticipant($event->getUser());

        if (null === $participant) {
            $firstName = $event->getUser()->getAccount()->getFirstName();
            $lastName  = $event->getUser()->getAccount()->getLastName();
        } else {
            $firstName = $this->participantInfoGuesser->guessParticipantFirstName($participant, $locale);
            $lastName  = $this->participantInfoGuesser->guessParticipantLastName($participant, $locale);
        }

        if (null !== $follower) {
            $admins[] = $follower;
        } else {
            // notify all organizer allowed to manage this sheet
            $admins = array_merge($admins,
                $this->adminRepository->getAllowedOrganizer(
                    $event->getSheet()->getEvent()
                )
            );
        }

        /** @var Admin $admin */
        foreach ($admins as $admin) {
            $mail = new SheetSubmittedMail(
                $event->getSheet()->getEvent(),
                $this->sender,
                $admin->getEmail(),
                $admin->getLocale(),
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
    public static function getSubscribedEvents(): array
    {
        return [
            Events::SHEET_VALIDATION_PENDING => 'onSheetSubmittedValidation',
        ];
    }
}
