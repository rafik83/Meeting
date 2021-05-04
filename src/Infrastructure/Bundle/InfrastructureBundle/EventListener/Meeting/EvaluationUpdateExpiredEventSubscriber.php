<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Meeting;

use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\Components\Mail\AbstractMail;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Components\Transactional\Mail\PrepareHandler;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareMeetingFollowUpView;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Meeting\MeetingEvaluationUpdateExpiredEvent;
use Proximum\Vimeet\Application\View\Meeting\FollowUpParticipantListView;
use Proximum\Vimeet\Application\View\Meeting\FollowUpParticipantView;
use Proximum\Vimeet\Domain\Model\Event\EventUrlGeneratorInterface;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EvaluationUpdateExpiredEventSubscriber implements EventSubscriberInterface
{

    private MailerInterface $mailer;
    private PrepareHandler $prepareHandler;
    private ParticipantInfoGuesser $participantInfoGuesser;
    private EventUrlGeneratorInterface $eventUrlGeneratorInterface;

    public function __construct(
        MailerInterface $mailer,
        PrepareHandler $prepareHandler,
        ParticipantInfoGuesser $participantInfoGuesser,
        EventUrlGeneratorInterface $eventUrlGeneratorInterface
    ) {
        $this->mailer = $mailer;
        $this->prepareHandler = $prepareHandler;
        $this->participantInfoGuesser = $participantInfoGuesser;
        $this->eventUrlGeneratorInterface = $eventUrlGeneratorInterface;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::MEETING_EVALUATION_UPDATE_EXPIRED => 'onMeetingEvaluationUpdateExpired',
        ];
    }

    public function onMeetingEvaluationUpdateExpired(MeetingEvaluationUpdateExpiredEvent $event): void
    {
        $evaluatedSheet = $event->getMeeting()->getSheetOfUser($event->getUser());

        $mail = $this->prepareHandler->handle(new PrepareMeetingFollowUpView(
            $event->getEvent(),
            $event->getUser(),
            $event->getLocale(),
            $evaluatedSheet,
            $event->getEvaluatingSheet()->getTitle(),
            $event->getEvaluation(),
            $this->createParticipantList($event->getEvaluatingSheet(), $evaluatedSheet, $event->getLocale())
        ));

        if (!$mail instanceof AbstractMail) {
            return;
        }

        $this->mailer->setHost($event->getEvent()->getDomain());
        $this->mailer->send($mail);

    }

    private function createParticipantList(Sheet $sheet, Sheet $evaluatedSheet, string  $locale): FollowUpParticipantListView
    {
        // TODO: add rules checker to hide email / phone
        $participantViews = $sheet->getParticipants()->map(
            function (Participant $participant) use ($sheet, $evaluatedSheet, $locale) {
                $infos = $this->participantInfoGuesser->guessParticipantInfos($participant, $locale);

                return new FollowUpParticipantView(
                    $infos[Tag::PARTICIPANT_FIRSTNAME],
                    $infos[Tag::PARTICIPANT_LASTNAME],
                    $infos[Tag::PARTICIPANT_POSITION],
                    $sheet->getId(),
                    $this->eventUrlGeneratorInterface->generateEventAbsoluteUrl(
                        $sheet->getEvent(),
                        'event_catalog_complete_sheet',
                        [
                            'sheet' => $evaluatedSheet->getId(),
                            'sheetToDisplay' => $sheet->getId(),
                            '_locale' => $locale,
                        ]
                    ),
                    $infos[Tag::PARTICIPANT_AVATAR],
                    $participant->getEmail(),
                    $infos[Tag::PARTICIPANT_PHONE]
                );
            }
        );

        return new FollowUpParticipantListView($participantViews->toArray());
    }
}
